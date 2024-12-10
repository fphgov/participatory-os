<?php

declare(strict_types=1);

namespace App\Handler\Media;

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
        $id    = $request->getAttribute('id');
        $media = $this->mediaService->getMedia($id);

        if ($media === null) {
            return new JsonResponse([
                'errors' => 'Nem található',
            ], 404);
        }

        $mediaStream = null;

        try {
            $mediaStream = $this->mediaService->getMediaStream($media);
        } catch (Exception $e) {
            $this->audit->err('Not found media element', [
                'extra' => $e->getMessage() . ' | ' . $id,
            ]);

            return new Response('php://memory', 404);
        }

        return new Response($mediaStream, 200, [
            'Content-Type'              => $media->getType(),
            'Content-Transfer-Encoding' => 'Binary',
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Cache-Control'             => 'must-revalidate',
            'Content-Length'            => $mediaStream->getSize(),
        ]);
    }
}
