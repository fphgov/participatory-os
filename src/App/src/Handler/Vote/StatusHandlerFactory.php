<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class StatusHandlerFactory
{
    public function __invoke(ContainerInterface $container): StatusHandler
    {
        return new StatusHandler(
            $container->get(EntityManagerInterface::class),
        );
    }
}
