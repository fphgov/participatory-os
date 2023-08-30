<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Service\VoteValidationServiceInterface;
use App\Service\PhaseServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class CheckHandlerFactory
{
    public function __invoke(ContainerInterface $container): CheckHandler
    {
        return new CheckHandler(
            $container->get(EntityManagerInterface::class),
            $container->get(VoteValidationServiceInterface::class),
            $container->get(PhaseServiceInterface::class)
        );
    }
}
