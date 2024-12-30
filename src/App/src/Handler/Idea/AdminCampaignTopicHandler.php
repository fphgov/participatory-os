<?php

declare(strict_types=1);

namespace App\Handler\Idea;

use App\Entity\Idea;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminCampaignTopicHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HalResponseFactory $responseFactory,
        private ResourceGenerator $resourceGenerator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entityRepository = $this->entityManager->getRepository(Idea::class);

        $idea = $entityRepository->find($request->getAttribute('id'));

        if ($idea === null) {
            return new JsonResponse([
                'errors' => 'Nincs ilyen azonosítójú ötlet, vagy még feldolgozás alatt áll',
            ], 404);
        }

        return new JsonResponse([
            'data' => $idea->getCampaign()->getCampaignTopicsOptions()
        ]);
    }
}
