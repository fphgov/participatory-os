<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\MediaServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class ProjectServiceFactory
{
    /**
     * @return ProjectService
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ProjectService(
            $container->get(EntityManagerInterface::class),
            $container->get(MediaServiceInterface::class)
        );
    }
}
