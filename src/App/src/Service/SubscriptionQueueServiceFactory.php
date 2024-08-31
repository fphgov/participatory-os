<?php

declare(strict_types=1);

namespace App\Service;

use App\Middleware\AuditMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

final class SubscriptionQueueServiceFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SubscriptionQueueService
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (
            ! isset($config['newsletter']) ||
            ! isset($config['newsletter']['url']) ||
            ! isset($config['newsletter']['cid']) ||
            ! $config['newsletter']['url'] ||
            ! $config['newsletter']['cid']
        ) {
            throw new ServiceNotFoundException('Missing SubscriptionQueueService setting!');
        }

        return new SubscriptionQueueService(
            $config['newsletter'],
            $container->get(EntityManagerInterface::class),
            $container->get(AuditMiddleware::class)->getLogger(),
        );
    }
}
