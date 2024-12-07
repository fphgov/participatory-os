<?php

declare(strict_types=1);

namespace App\Handler\Mail;

use App\Entity\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminGetHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $mailRepository = $this->em->getRepository(Mail::class);

        $mail = $mailRepository->findOneBy([
            'code' => $request->getAttribute('code'),
        ]);

        if ($mail === null) {
            return new JsonResponse([
                'errors' => 'Nincs ilyen azonosítójú e-mail',
            ], 404);
        }

        $normalizedMail = $mail->normalizer(null, ['groups' => 'detail']);

        return new JsonResponse([
            'data' => $normalizedMail,
        ]);
    }
}
