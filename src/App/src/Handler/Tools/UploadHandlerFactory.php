<?php

declare(strict_types=1);

namespace App\Handler\Tools;

use App\InputFilter\AdminUploadFileFilter;
use App\Service\MediaServiceInterface;
use Psr\Container\ContainerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

final class UploadHandlerFactory
{
    public function __invoke(ContainerInterface $container): UploadHandler
    {
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $mediaService  = $container->get(MediaServiceInterface::class);
        $inputFilter   = $pluginManager->get(AdminUploadFileFilter::class);

        return new UploadHandler(
            $inputFilter,
            $mediaService
        );
    }
}
