<?php

declare(strict_types=1);

namespace App\Handler\Idea;

use App\Service\IdeaServiceInterface;
use Psr\Container\ContainerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

final class AdminListHandlerFactory
{
    public function __invoke(ContainerInterface $container): AdminListHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new AdminListHandler(
            $container->get(IdeaServiceInterface::class),
            isset($config['app']['pagination']['maxPageSize']) ? (int) $config['app']['pagination']['maxPageSize'] : 25,
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
        );
    }
}
