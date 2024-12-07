<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Phase;
use App\Entity\PhaseInterface;
use App\Exception\DifferentPhaseException;
use App\Exception\InvalidPhaseException;
use App\Exception\NotHavePhaseException;
use App\Repository\PhaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PhaseService implements PhaseServiceInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var PhaseRepository */
    private $phaseRepository;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em              = $em;
        $this->phaseRepository = $this->em->getRepository(Phase::class);
    }

    public function getCurrentPhase(): PhaseInterface
    {
        $phase = $this->phaseRepository->getCurrentPhase();

        if (! $phase instanceof PhaseInterface) {
            throw new NotHavePhaseException();
        }

        return $phase;
    }

    public function phaseCheck(int $phaseCode): PhaseInterface
    {
        $phase = $this->getCurrentPhase();

        if (! isset(PhaseInterface::PHASES[$phaseCode])) {
            throw new InvalidPhaseException((string) $phaseCode);
        }

        if ($phase->getCode() !== PhaseInterface::PHASES[$phaseCode]) {
            throw new DifferentPhaseException((string) $phase->getCode());
        }

        return $phase;
    }

    public function getRepository(): EntityRepository
    {
        return $this->phaseRepository;
    }
}
