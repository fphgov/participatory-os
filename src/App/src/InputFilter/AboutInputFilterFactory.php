<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class AboutInputFilterFactory
{
    public function __invoke(ContainerInterface $container): AboutInputFilter
    {
        return new AboutInputFilter(
            $container->get(AdapterInterface::class)
        );
    }
}
