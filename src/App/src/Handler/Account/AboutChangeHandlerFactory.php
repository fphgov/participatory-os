<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\InputFilter\AboutInputFilter;
use App\Service\UserServiceInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Container\ContainerInterface;

final class AboutChangeHandlerFactory
{
    public function __invoke(ContainerInterface $container): AboutChangeHandler
    {
        /** @var InputFilterPluginManager $pluginManager */
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(AboutInputFilter::class);

        return new AboutChangeHandler(
            $container->get(UserServiceInterface::class),
            $inputFilter,
        );
    }
}
