<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Psr\Container\ContainerInterface;

final class PrizeSimpleHandlerFactory
{
    public function __invoke(ContainerInterface $container): PrizeSimpleHandler
    {
        return new PrizeSimpleHandler(
            $container->get(UserServiceInterface::class)
        );
    }
}
