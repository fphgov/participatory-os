<?php

declare(strict_types=1);

namespace Jwt\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class TokenServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TokenService
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (! isset($config['jwt'])) {
            throw new RuntimeException('Missing JWT configuration');
        }

        return new TokenService(
            $config['jwt'],
            $container->get(EntityManagerInterface::class),
        );
    }
}
