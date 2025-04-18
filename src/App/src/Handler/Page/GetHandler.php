<?php

declare(strict_types=1);

namespace App\Handler\Page;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pageRepository = $this->entityManager->getRepository(Page::class);

        $page = $pageRepository->findOneBy([
            'slug'   => $request->getAttribute('slug'),
            'status' => 'publish'
        ]);

        if ($page === null) {
            return new JsonResponse([
                'errors' => 'Nem található',
            ], 404);
        }

        $normalizedPage = $page->normalizer(null, ['groups' => 'detail']);

        return new JsonResponse([
            'data' => $normalizedPage,
        ]);
    }
}
