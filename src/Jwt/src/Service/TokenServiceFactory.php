<?php

declare(strict_types=1);

namespace Jwt\Service;

use Psr\Container\ContainerInterface;
use RuntimeException;

class TokenServiceFactory
{
    public function __invoke(ContainerInterface $container): TokenService
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (! isset($config['jwt'])) {
            throw new RuntimeException('Missing JWT configuration');
        }

        return new TokenService(
            $config['jwt'],
        );
    }
}
