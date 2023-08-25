<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OfflineVote;
use App\Entity\PhaseInterface;
use App\Entity\Project;
use App\Entity\ProjectInterface;
use App\Entity\UserInterface;
use App\Entity\Vote;
use App\Entity\VoteInterface;
use App\Entity\VoteType;
use App\Entity\VoteTypeInterface;
use App\Model\VoteableProjectFilterModel;
use App\Exception\NoExistsAllProjectsException;
use App\Service\MailServiceInterface;
use App\Service\PhaseServiceInterface;
use App\Service\VoteValidationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Mezzio\Hal\ResourceGenerator;

use function count;
use function strtolower;

final class VoteService implements VoteServiceInterface
{
    /** @var EntityRepository */
    private $voteRepository;

    /** @var EntityRepository */
    private $projectRepository;

    public function __construct(
        private array $config,
        private EntityManagerInterface $em,
        private PhaseServiceInterface $phaseService,
        private MailServiceInterface $mailService,
        private VoteValidationService $voteValidationService,
        private ResourceGenerator $resourceGenerator
    ) {
        $this->config                = $config;
        $this->em                    = $em;
        $this->phaseService          = $phaseService;
        $this->mailService           = $mailService;
        $this->voteValidationService = $voteValidationService;
        $this->resourceGenerator     = $resourceGenerator;
        $this->projectRepository     = $this->em->getRepository(Project::class);
        $this->voteRepository        = $this->em->getRepository(Vote::class);
    }

    public function addOfflineVote(
        UserInterface $user,
        int $projectId,
        int $type,
        int $voteCount
    ): void {
        $date = new DateTime();

        $project = $this->projectRepository->find($projectId);

        for ($i = 0; $i < $voteCount; $i++) {
            $this->createOfflineVote($user, $project, $date, $type);
        }

        $this->em->flush();
    }

    private function createOfflineVote(
        UserInterface $user,
        ProjectInterface $project,
        DateTime $date,
        int $type
    ): VoteInterface {
        $vote = new OfflineVote();

        $vote->setUser($user);
        $vote->setProject($project);
        $vote->setVoteType(
            $this->em->getReference(VoteType::class, $type)
        );
        $vote->setCreatedAt($date);
        $vote->setUpdatedAt($date);

        $this->em->persist($vote);

        return $vote;
    }

    private function createOnlineVote(
        UserInterface $user,
        ProjectInterface $project,
        VoteTypeInterface $voteType,
        DateTime $date
    ): VoteInterface {
        $vote = new Vote();

        $vote->setUser($user);
        $vote->setProject($project);
        $vote->setVoteType($voteType);
        $vote->setCreatedAt($date);
        $vote->setUpdatedAt($date);

        $user->addVote($vote);

        $this->em->persist($vote);

        return $vote;
    }

    public function voting(
        UserInterface $user,
        VoteTypeInterface $voteType,
        array $projects
    ): void {
        $phase = $this->phaseService->phaseCheck(PhaseInterface::PHASE_VOTE);

        $dbProjects = $this->projectRepository->findBy([
            'id' => $projects,
        ]);

        if (count($dbProjects) !== count($projects)) {
            throw new NoExistsAllProjectsException('There are 1 or more ideas specified');
        }

        $this->voteValidationService->validation(
            $user,
            $phase,
            $voteType,
            $dbProjects
        );

        $date = new DateTime();

        $votes = [];
        foreach ($dbProjects as $project) {
            $votes[] = $this->createOnlineVote($user, $project, $voteType, $date);
        }

        $this->em->flush();

        $this->successVote($user, $votes);
    }

    /**
     * @param array[]|VoteInterface $votes
     **/
    private function successVote(UserInterface $user, array $votes): void
    {
        $projects = [];

        foreach ($votes as $vote) {
            $projects[] = [
                'title'        => $vote->getProject()->getTitle(),
                'campaignName' => $vote->getProject()->getCampaignTheme()->getName(),
                'projectType'  => strtolower($vote->getProject()->getProjectType()->getTitle()),
            ];
        }

        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'projects'         => $projects,
        ];

        $this->mailService->send('vote-success', $tplData, $user);
    }

    public function getVoteablesProjects(VoteableProjectFilterModel $voteableProjectFilter): QueryBuilder
    {
        $phase = $this->phaseService->phaseCheck(PhaseInterface::PHASE_VOTE);

        return $this->projectRepository->getVoteables(
            $phase->getCampaign(),
            $voteableProjectFilter
        );
    }

    public function getRepository(): EntityRepository
    {
        return $this->voteRepository;
    }
}
