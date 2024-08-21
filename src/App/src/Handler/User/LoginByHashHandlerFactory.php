<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Psr\Container\ContainerInterface;

final class LoginByHashHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginByHashHandler
    {
        return new LoginByHashHandler(
            $container->get(UserServiceInterface::class)
        );
    }
}
