<?php

declare(strict_types=1);

namespace App\Handler\Project;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AdminGetHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HalResponseFactory $responseFactory,
        private ResourceGenerator $resourceGenerator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $entityRepository = $this->entityManager->getRepository(Project::class);

        $result = $entityRepository->find($request->getAttribute('id'));

        if ($result === null) {
            return new JsonResponse([
                'errors' => 'Nincs ilyen azonosítójú projekt, vagy még feldolgozás alatt áll',
            ], 404);
        }

        $project = $result->normalizer(null, ['groups' => 'full_detail']);

        $resource = $this->resourceGenerator->fromArray($project, null);

        return $this->responseFactory->createResponse($request, $resource);
    }
}
