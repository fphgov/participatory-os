<?php

declare(strict_types=1);

opcache_invalidate(__FILE__, true);

if (PHP_SAPI !== 'cli') {
    return false;
}

chdir(__DIR__ . '/../../');

use App\Service\MailQueueServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

require 'vendor/autoload.php';

$container = require 'config/container.php';

$em               = $container->get(EntityManagerInterface::class);
$mailQueueService = $container->get(MailQueueServiceInterface::class);

try {
    $mailQueueService->process();
    usleep(250000); # 0.25 sec
} catch (\Throwable $th) {

}
