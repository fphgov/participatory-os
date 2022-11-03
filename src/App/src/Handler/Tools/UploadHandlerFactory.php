<?php

declare(strict_types=1);

namespace App\Handler\Tools;

use App\InputFilter\AdminUploadFileFilter;
use Psr\Container\ContainerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

final class UploadHandlerFactory
{
    public function __invoke(ContainerInterface $container): UploadHandler
    {
        /** @var InputFilterPluginManager $pluginManager */
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(AdminUploadFileFilter::class);

        return new UploadHandler(
            $inputFilter
        );
    }
}
