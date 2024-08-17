<?php

declare(strict_types=1);

namespace App\Service;

use App\InputFilter\VoteFilter;
use App\Service\VoteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Container\ContainerInterface;

final class VoteServiceMessageFactory
{
    /**
     * @return VoteServiceMessage
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var InputFilterPluginManager $pluginManager */
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(VoteFilter::class);

        return new VoteServiceMessage(
            $container->get(EntityManagerInterface::class),
            $container->get(VoteServiceInterface::class),
            $inputFilter
        );
    }
}
