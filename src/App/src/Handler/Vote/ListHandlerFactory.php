<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Service\VoteServiceInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerInterface;

final class ListHandlerFactory
{
    public function __invoke(ContainerInterface $container): ListHandler
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new ListHandler(
            $container->get(VoteServiceInterface::class),
            isset($config['app']['pagination']['maxPageSizeForVote']) ? (int) $config['app']['pagination']['maxPageSizeForVote'] : 12,
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
        );
    }
}
