<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CampaignInterface;
use App\Entity\PhaseInterface;
use App\Entity\ProjectInterface;
use App\Entity\ProjectTypeInterface;
use App\Entity\UserInterface;
use App\Entity\Vote;
use App\Entity\VoteTypeInterface;
use App\Exception\MissingVoteTypeAndCampaignCategoriesException;
use App\Exception\DuplicateCampaignCategoriesException;
use App\Exception\NotHaveProjectInCurrentCampaignException;
use App\Exception\VoteUserExistsException;
use App\Exception\VoteUserProjectExistsException;
use App\Exception\VoteUserCategoryExistsException;
use App\Exception\VoteUserCategoryAlreadyTotalVotesException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use function array_keys;
use function count;
use function in_array;
use function array_unique;

final class VoteValidationService implements VoteValidationServiceInterface
{
    private EntityRepository $voteRepository;

    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->voteRepository = $this->em->getRepository(Vote::class);
    }

    public function checkExistsVote(
        UserInterface $user,
        PhaseInterface $phase,
        VoteTypeInterface $voteType,
        ?ProjectInterface $project = null,
    ): void {
        if ($voteType->getId() === 4) {
            $this->checkCountedExistsVoteInProject($user, $phase->getCampaign(), $project, self::VOTE_TYPE_4_COUNT);
        } else if ($project instanceof ProjectInterface) {
            $this->checkExistsVoteInProject($user, $phase->getCampaign(), $project);
        } else {
            $this->checkExistsVoteWithoutProject($user, $phase->getCampaign());
        }
    }

    public function validation(
        UserInterface $user,
        PhaseInterface $phase,
        VoteTypeInterface $voteType,
        array $projects
    ): void {
        if ($voteType->getId() === 1) {
            $this->checkExistsVote($user, $phase, $voteType);
            $this->validationNormal($phase->getCampaign(), $projects);
        } elseif ($voteType->getId() === 2) {
            $this->checkExistsVote($user, $phase, $voteType);
            $this->validationBigCategory($phase->getCampaign(), $projects);
        } elseif ($voteType->getId() === 3) {
            foreach ($projects as $project) {
                $this->checkExistsVote($user, $phase, $voteType, $project);
            }

            $this->validationLocationCategory($phase->getCampaign(), $projects);
        } elseif ($voteType->getId() === 4) {
            foreach ($projects as $project) {
                $this->checkCountedExistsVoteInProject($user, $phase->getCampaign(), $project, self::VOTE_TYPE_4_COUNT);
            }

            $this->validationLocationCategory($phase->getCampaign(), $projects);
        }
    }

    public function getAvailableVoteCount(
        UserInterface $user,
        CampaignInterface $campaign,
        VoteTypeInterface $voteType,
        ProjectInterface $project
    ): int {

        $count = $voteType->getId() === 4 ? self::VOTE_TYPE_4_COUNT : self::VOTE_TYPE_DEFAULT_COUNT;

        $existsCategoryVoteCount = $this->voteRepository->getExistsVotesInCampaignInCategory($user, $campaign, $project);

        return ($count - $existsCategoryVoteCount);
    }

    private function checkExistsVoteWithoutProject(
        UserInterface $user,
        CampaignInterface $campaign
    ): void {
        $existsVote = $this->voteRepository->checkExistsVoteInCampaign($user, $campaign);

        if ($existsVote) {
            throw new VoteUserExistsException('User already voted this campaign');
        }
    }

    private function checkExistsVoteInProject(
        UserInterface $user,
        CampaignInterface $campaign,
        ProjectInterface $project
    ): void {
        $existsProjectVote = $this->voteRepository->checkExistsVoteInCampaignAndProject($user, $campaign, $project);

        if ($existsProjectVote) {
            throw new VoteUserProjectExistsException('User already voted this project');
        }

        $existsCategoryVote = $this->voteRepository->checkExistsVoteInCampaignInCategory($user, $campaign, $project);

        if ($existsCategoryVote) {
            throw new VoteUserCategoryExistsException('User already voted in category');
        }
    }

    private function checkCountedExistsVoteInProject(
        UserInterface $user,
        CampaignInterface $campaign,
        ProjectInterface $project,
        int $count
    ): void {
        $existsProjectVote = $this->voteRepository->checkExistsVoteInCampaignAndProject($user, $campaign, $project);

        if ($existsProjectVote) {
            throw new VoteUserProjectExistsException('User already voted this project');
        }

        $existsCategoryVoteCount = $this->voteRepository->getExistsVotesInCampaignInCategory($user, $campaign, $project);

        if ($existsCategoryVoteCount >= $count) {
            throw new VoteUserCategoryAlreadyTotalVotesException('User already total voted in category');
        }
    }

    private function validationNormal(
        CampaignInterface $campaign,
        array $projects
    ): void {
        $types = [];
        foreach ($projects as $project) {
            $types[$project->getCampaignTheme()->getId() . '-' . $project->getProjectType()->getId()] = $project;
        }

        if (count($types) !== count($projects)) {
            throw new MissingVoteTypeAndCampaignCategoriesException('There are no ideas in all categories and types');
        }

        $campaignThemes = $campaign->getCampaignThemes();

        $testTypes = [];
        foreach ($campaignThemes as $campaignThemeId) {
            $testTypes[] = $campaignThemeId . '-' . ProjectTypeInterface::IDEA_NORMAL;
        }

        $hasAll = true;
        foreach (array_keys($types) as $type) {
            if (! in_array($type, $testTypes, true)) {
                $hasAll = false;
            }
        }

        if ($hasAll === false) {
            throw new MissingVoteTypeAndCampaignCategoriesException('There are no ideas in all categories and types');
        }
    }

    private function validationBigCategory(
        CampaignInterface $campaign,
        array $projects
    ): void {
        $types = [];
        foreach ($projects as $project) {
            $types[$project->getCampaignTheme()->getId() . '-' . $project->getProjectType()->getId()] = $project;
        }

        if (count($types) !== count($projects)) {
            throw new MissingVoteTypeAndCampaignCategoriesException('There are no ideas in all categories and types');
        }

        $campaignThemes = $campaign->getCampaignThemes();

        $testTypes = [];
        foreach ($campaignThemes as $campaignThemeId) {
            $testTypes[] = $campaignThemeId . '-' . ProjectTypeInterface::IDEA_SMALL;
            $testTypes[] = $campaignThemeId . '-' . ProjectTypeInterface::IDEA_BIG;
        }

        $hasAll = true;
        foreach (array_keys($types) as $type) {
            if (! in_array($type, $testTypes, true)) {
                $hasAll = false;
            }
        }

        if ($hasAll === false) {
            throw new MissingVoteTypeAndCampaignCategoriesException('There are no ideas in all categories and types');
        }
    }

    private function validationLocationCategory(
        CampaignInterface $campaign,
        array $projects
    ): void {
        $types = [];
        foreach ($projects as $project) {
            if ($project->getCampaign()->getId() !== $campaign->getId()) {
                throw new NotHaveProjectInCurrentCampaignException('The idea is not part of the current campaign');
            }

            $types[] = $project->getCampaignTheme()->getId() . '-' . $project->getProjectType()->getId();
        }

        $hasDuplicates = count($types) !== count(array_unique($types));

        if ($hasDuplicates) {
            throw new DuplicateCampaignCategoriesException('The idea fell into that category');
        }
    }
}
