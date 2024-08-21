<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Aws\S3\S3Client;

final class MinIOServiceFactory
{
    public function __invoke(ContainerInterface $container): MinIOService
    {
        $config = $container->has('config') ? $container->get('config') : [];

        $s3 = new S3Client([
            'region'                  => $config['minio']['region'],
            'endpoint'                => $config['minio']['endpoint'],
            'use_path_style_endpoint' => true,
            'useSSL'                  => $config['minio']['useSSL'],
            'credentials'             => [
                'key'    => $config['minio']['accessKey'],
                'secret' => $config['minio']['secretKey'],
            ],
        ]);

        return new MinIOService(
            $s3,
        );
    }
}
