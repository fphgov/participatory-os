<?php

declare(strict_types=1);

namespace App\Service;

use Aws\S3\S3ClientInterface;

interface MinIOServiceInterface
{
    public function getClient(): S3ClientInterface;
}
