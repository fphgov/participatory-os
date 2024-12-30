<?php

declare(strict_types=1);

namespace App\Handler\Plan;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetHandler implements RequestHandlerInterface
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
                'errors' => 'Nem található',
            ], 404);
        }

        $project = $result->normalizer(null, ['groups' => 'detail']);

        $resource = $this->resourceGenerator->fromArray($project, null);
        $resource = $resource->withElement('voted', null);

        return $this->responseFactory->createResponse($request, $resource);
    }
}
