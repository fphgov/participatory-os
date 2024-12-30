<?php

declare(strict_types=1);

namespace App\Handler\Media;

use App\Service\MediaServiceInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

final class DownloadHandler implements RequestHandlerInterface
{
    public function __construct(
        private MediaServiceInterface $mediaService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $media = $this->mediaService->getMedia($request->getAttribute('id'));

        if ($media === null) {
            return new JsonResponse([
                'errors' => 'Nem található',
            ], 404);
        }

        try {
            $mediaStream = $this->mediaService->getMediaStream($media);
        } catch (Exception $e) {
            return new Response('php://memory', 404);
        }

        return new Response($mediaStream, 200, [
            'Content-Type'              => $media->getType(),
            'Content-Disposition'       => 'attachment; filename="' . $media->getFilename() . '"',
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Description'       => 'File Transfer',
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Cache-Control'             => 'must-revalidate',
            'Content-Length'            => $mediaStream->getSize(),
        ]);
    }
}
