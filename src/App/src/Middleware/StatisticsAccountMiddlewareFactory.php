<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;

class StatisticsAccountMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): StatisticsAccountMiddleware
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new StatisticsAccountMiddleware(
            $config,
        );
    }
}
