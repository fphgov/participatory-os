<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Model\VoteExportModel;
use App\Service\PhaseServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class StatisticsHandlerFactory
{
    public function __invoke(ContainerInterface $container): StatisticsHandler
    {
        return new StatisticsHandler(
            $container->get(EntityManagerInterface::class),
            $container->get(PhaseServiceInterface::class),
            $container->get(VoteExportModel::class)
        );
    }
}
