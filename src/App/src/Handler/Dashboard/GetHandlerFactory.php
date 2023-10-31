<?php

declare(strict_types=1);

namespace App\Handler\Dashboard;

use App\Service\SettingServiceInterface;
use App\Service\PhaseServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class GetHandlerFactory
{
    public function __invoke(ContainerInterface $container): GetHandler
    {
        return new GetHandler(
            $container->get(EntityManagerInterface::class),
            $container->get(SettingServiceInterface::class),
            $container->get(PhaseServiceInterface::class)
        );
    }
}
