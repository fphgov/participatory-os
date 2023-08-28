<?php

declare(strict_types=1);

namespace App\Handler\File;

use App\Service\MediaServiceInterface;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GetHandler implements RequestHandlerInterface
{
    /** @var MediaServiceInterface */
    private $mediaService;

    /** @var Logger */
    private $audit;

    public function __construct(
        MediaServiceInterface $mediaService,
        Logger $audit
    ) {
        $this->mediaService = $mediaService;
        $this->audit        = $audit;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $filename    = $queryParams['filename'] ?? null;

        if (! $filename) {
            return new JsonResponse([
                'errors' => 'Nem található',
            ], 404);
        }

        try {
            $mediaStream = $this->mediaService->getFile($filename);
        } catch (Exception $e) {
            $this->audit->err('No exists file in object storage', [
                'extra' => $filename,
            ]);

            return new JsonResponse([
                'errors' => 'Nem található',
            ], 404);
        }

        return new Response($mediaStream);
    }
}
