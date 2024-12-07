<?php

declare(strict_types=1);

namespace App\Handler\Workflow;

use App\Entity\WorkflowState;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetStatesHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $workflowStateRepository = $this->em->getRepository(WorkflowState::class);

        $normalizedWorkFlowStates = $workflowStateRepository->getAllWorkflowState();

        return new JsonResponse([
            'data' => $normalizedWorkFlowStates,
        ]);
    }
}
