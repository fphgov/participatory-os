<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Psr\Container\ContainerInterface;

final class NewsletterSimpleHandlerFactory
{
    public function __invoke(ContainerInterface $container): NewsletterSimpleHandler
    {
        return new NewsletterSimpleHandler(
            $container->get(UserServiceInterface::class)
        );
    }
}
