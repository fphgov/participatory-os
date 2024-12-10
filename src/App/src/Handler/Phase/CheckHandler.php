<?php

declare(strict_types=1);

namespace App\Handler\Phase;

use App\Service\PhaseServiceInterface;
use App\Exception\DifferentPhaseException;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CheckHandler implements RequestHandlerInterface
{
    public function __construct(
        private PhaseServiceInterface $phaseService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $phase = $this->phaseService->getCurrentPhase();
        } catch (DifferentPhaseException $e) {
            return new JsonResponse([
                'message' => 'A szavazás zárva',
                'code'    => 'CLOSED'
            ], 422);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Nem várt hiba történt',
                'code'    => 'SERVER_ERROR'
            ], 500);
        }

        return new JsonResponse([
            'data' => [
                'campaign' => $phase->getCampaign()->getId(),
                'code'     => $phase->getCode(),
            ],
        ]);
    }
}
