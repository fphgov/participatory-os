<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CampaignInterface;
use App\Entity\PhaseInterface;
use App\Entity\ProjectInterface;
use App\Entity\ProjectTypeInterface;
use App\Entity\Setting;
use App\Entity\UserInterface;
use App\Entity\Vote;
use App\Entity\VoteTypeInterface;
use App\Exception\MissingVoteTypeAndCampaignCategoriesException;
use App\Exception\DuplicateCampaignCategoriesException;
use App\Exception\NoHasProjectInCurrentCampaignException;
use App\Exception\VoteUserExistsException;
use App\Exception\VoteUserProjectExistsException;
use App\Exception\VoteUserCategoryExistsException;
use App\Exception\VoteTypeNoExistsInDatabaseException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

use function array_keys;
use function count;
use function in_array;
use function array_unique;

final class VoteValidationService implements VoteValidationServiceInterface
{
    /** @var EntityRepository */
    private $voteRepository;

    /** @var EntityRepository */
    private $settingRepository;

    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->em                = $em;
        $this->voteRepository    = $this->em->getRepository(Vote::class);
        $this->settingRepository = $this->em->getRepository(Setting::class);
    }

    public function checkExistsVote(
        UserInterface $user,
        PhaseInterface $phase,
        ?ProjectInterface $project = null
    ): void {
        if ($project instanceof ProjectInterface) {
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
        $voteType = $this->settingRepository->findOneBy([
            'key' => 'vote-type',
        ]);

        if (! $voteType) {
            throw new VoteTypeNoExistsInDatabaseException('Vote type no exists in database');
        }

        if ($voteType->getValue() === '1') {
            $this->checkExistsVote($user, $phase);
            $this->validationNormal($phase->getCampaign(), $projects);
        } elseif ($voteType->getValue() === '2') {
            $this->checkExistsVote($user, $phase);
            $this->validationBigCategory($phase->getCampaign(), $projects);
        } elseif ($voteType->getValue() === '3') {
            foreach ($projects as $project) {
                $this->checkExistsVote($user, $phase, $project);
            }

            $this->validationLocationCategory($phase->getCampaign(), $projects);
        }
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
                throw new NoHasProjectInCurrentCampaignException('The idea is not part of the current campaign');
            }

            $types[] = $project->getCampaignTheme()->getId() . '-' . $project->getProjectType()->getId();
        }

        $hasDuplicates = count($types) !== count(array_unique($types));

        if ($hasDuplicates) {
            throw new DuplicateCampaignCategoriesException('The idea fell into that category');
        }
    }
}
