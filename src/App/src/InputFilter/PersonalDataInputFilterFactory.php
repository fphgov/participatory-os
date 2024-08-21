<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class PersonalDataInputFilterFactory
{
    public function __invoke(ContainerInterface $container): PersonalDataInputFilter
    {
        return new PersonalDataInputFilter(
            $container->get(AdapterInterface::class)
        );
    }
}
