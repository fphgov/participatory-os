<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\InputFilter\PersonalDataInputFilter;
use App\Service\UserServiceInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Container\ContainerInterface;

final class PersonalChangeHandlerFactory
{
    public function __invoke(ContainerInterface $container): PersonalChangeHandler
    {
        /** @var InputFilterPluginManager $pluginManager */
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(PersonalDataInputFilter::class);

        return new PersonalChangeHandler(
            $container->get(UserServiceInterface::class),
            $inputFilter,
        );
    }
}
