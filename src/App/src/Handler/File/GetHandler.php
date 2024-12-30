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
    public function __construct(
        private MediaServiceInterface $mediaService,
        private Logger $audit
    ) {}

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
            $mediaResult = $this->mediaService->getFile($filename);

            $mediaStream = $mediaResult->get('Body');
            $mediaStream->rewind();

            return new Response($mediaStream, 200, [
                'Content-Type' => $mediaResult->get('ContentType'),
            ]);
        } catch (Exception $e) {
            $this->audit->err('No exists file in object storage', [
                'extra' => $filename,
            ]);

            return new JsonResponse([
                'errors' => 'Nem található',
            ], 404);
        }
    }
}
