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

use function array_merge_recursive;

final class PutHandler implements RequestHandlerInterface
{
    public function __construct(
        private MediaServiceInterface $mediaService,
        private Logger $audit
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = array_merge_recursive(
            $request->getParsedBody(),
            $request->getUploadedFiles(),
        );

        try {
            $this->mediaService->putFile($body['file']);
        } catch (Exception $e) {
            $this->audit->err('Failed put file to object storage', [
                'extra' => $e->getMessage(),
            ]);

            return new Response('php://memory', 404);
        }

        return new JsonResponse([
            'message' => 'Success'
        ]);
    }
}
