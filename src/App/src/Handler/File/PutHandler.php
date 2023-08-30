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
