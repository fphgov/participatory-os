<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;

interface VoteServiceMessageInterface
{
    public function votingWithJsonMessage(
        UserInterface $user,
        array $body
    ): JsonResponse;
}
