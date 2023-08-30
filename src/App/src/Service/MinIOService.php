<?php

declare(strict_types=1);

namespace App\Service;

use Aws\S3\S3ClientInterface;

class MinIOService implements MinIOServiceInterface
{
    public function __construct(
        private S3ClientInterface $client
    ) {
        $this->client = $client;
    }

    public function getClient(): S3ClientInterface
    {
        return $this->client;
    }
}
