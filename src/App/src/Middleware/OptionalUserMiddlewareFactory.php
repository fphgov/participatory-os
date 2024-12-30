<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\UserServiceInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class OptionalUserMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): OptionalUserMiddleware
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (! isset($config['jwt'])) {
            throw new RuntimeException('Missing JWT configuration');
        }

        $options = [
            "secret"    => $config['jwt']['auth']['secret'],
            "algorithm" => $config['jwt']['auth']['algorithm']
        ];

        return new OptionalUserMiddleware(
            $container->get(UserServiceInterface::class),
            $options
        );
    }
}
