<?php

declare(strict_types=1);

opcache_invalidate(__FILE__, true);

if (PHP_SAPI !== 'cli') {
    return false;
}

chdir(__DIR__ . '/../../');

use App\Service\SubscriptionQueueServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

require 'vendor/autoload.php';

$container = require 'config/container.php';

$em                       = $container->get(EntityManagerInterface::class);
$subscriptionQueueService = $container->get(SubscriptionQueueServiceInterface::class);

try {
    $subscriptionQueueService->process();
    usleep(250000); # 0.25 sec
} catch (\Throwable $th) {

}
