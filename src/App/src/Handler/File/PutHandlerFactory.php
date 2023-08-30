<?php

declare(strict_types=1);

namespace App\Handler\File;

use App\Middleware\AuditMiddleware;
use App\Service\MediaServiceInterface;
use Psr\Container\ContainerInterface;

final class PutHandlerFactory
{
    public function __invoke(ContainerInterface $container): PutHandler
    {
        return new PutHandler(
            $container->get(MediaServiceInterface::class),
            $container->get(AuditMiddleware::class)->getLogger()
        );
    }
}
