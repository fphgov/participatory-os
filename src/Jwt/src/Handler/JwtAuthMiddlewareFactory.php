<?php

declare(strict_types=1);

namespace Jwt\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Jwt\Service\TokenService;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Tuupola\Middleware\JwtAuthentication;

class JwtAuthMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): JwtAuthMiddleware
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $em = $container->get(EntityManagerInterface::class);

        if (! isset($config['jwt'])) {
            throw new RuntimeException('Missing JWT configuration');
        }

        $auth = new JwtAuthentication([
            "secure"    => false,
            "relaxed"   => ["localhost", "*.budapest.hu"],
            "secret"    => $config['jwt']['auth']['secret'],
            "algorithm" => $config['jwt']['auth']['algorithm'],
            "attribute" => JwtAuthMiddleware::class,
        ]);

        return new JwtAuthMiddleware(
            $auth,
            new TokenService($config['jwt'], $em)
        );
    }
}
