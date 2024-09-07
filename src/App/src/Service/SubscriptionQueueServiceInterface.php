<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Newsletter;

interface SubscriptionQueueServiceInterface
{
    public const SUBSCRIBE_ENDPOINT   = 'subscribe.php';
    public const UNSUBSCRIBE_ENDPOINT = 'unsubscribe.php';

    public function subscribe(Newsletter $newsletter): void;

    public function unsubscribe(Newsletter $newsletter): void;

    public function process(): void;
}
