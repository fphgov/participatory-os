<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\VoteServiceMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

final class VoteHandlerFactory
{
    public function __invoke(ContainerInterface $container): VoteHandler
    {
        return new VoteHandler(
            $container->get(EntityManagerInterface::class),
            $container->get(VoteServiceMessageInterface::class),
        );
    }
}
