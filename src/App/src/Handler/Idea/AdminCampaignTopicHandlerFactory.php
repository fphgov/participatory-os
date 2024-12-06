<?php

declare(strict_types=1);

namespace App\Handler\Idea;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerInterface;

final class AdminCampaignTopicHandlerFactory
{
    public function __invoke(ContainerInterface $container): AdminCampaignTopicHandler
    {
        return new AdminCampaignTopicHandler(
            $container->get(EntityManagerInterface::class),
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
        );
    }
}
