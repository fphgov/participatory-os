<?php

declare(strict_types=1);

namespace App\Handler\Mail;

use App\Entity\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminListHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $mailRepository = $this->em->getRepository(Mail::class);

        $normalizedMails = $mailRepository->getAllMail();

        return new JsonResponse([
            'data' => $normalizedMails,
        ]);
    }
}
