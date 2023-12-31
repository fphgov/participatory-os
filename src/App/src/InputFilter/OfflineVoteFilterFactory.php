<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class OfflineVoteFilterFactory
{
    public function __invoke(ContainerInterface $container): OfflineVoteFilter
    {
        return new OfflineVoteFilter(
            $container->get(AdapterInterface::class)
        );
    }
}
