<?php

declare(strict_types=1);

namespace Jwt\Handler;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Tuupola\Middleware\JwtAuthentication;

// use function getenv;

class JwtAuthMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): JwtAuthMiddleware
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (! isset($config['jwt'])) {
            throw new RuntimeException('Missing JWT configuration');
        }

        $auth = new JwtAuthentication([
            // "secure"    => getenv('NODE_ENV') !== 'development',
            "secure"    => false,
            "relaxed"   => ["localhost", "*.budapest.hu"],
            "secret"    => $config['jwt']['auth']['secret'],
            "algorithm" => $config['jwt']['auth']['algorithm'],
            "attribute" => JwtAuthMiddleware::class,
        ]);

        return new JwtAuthMiddleware(
            $auth
        );
    }
}
