<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Entity\Newsletter;
use App\Middleware\UserMiddleware;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetPreferenceHandler implements RequestHandlerInterface
{
    private NewsletterRepository $newsletterRepository;

    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->newsletterRepository = $this->em->getRepository(Newsletter::class);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        $normalizedUserPreference = $user->getUserPreference()->normalizer(null, [
            'groups' => 'profile'
        ]);

        $newsletter = $this->newsletterRepository->findOneBy([
            'email' => $user->getEmail(),
        ]);

        $subscribe = false;

        if ($newsletter && $newsletter->getType() === Newsletter::TYPE_SUBSCRIBE) {
            $subscribe = true;
        }

        $normalizedUserPreference['newsletter'] = $subscribe;

        return new JsonResponse([
            'data' => $normalizedUserPreference,
        ]);
    }
}
