<?php

declare(strict_types=1);

namespace App\Handler\User;

use Psr\Container\ContainerInterface;

final class GetPreferenceHandlerFactory
{
    public function __invoke(ContainerInterface $container): GetPreferenceHandler
    {
        return new GetPreferenceHandler();
    }
}
