<?php

declare(strict_types=1);

namespace App\Handler\Tools;

use App\InputFilter\AdminUploadFileFilter;
use App\Service\MediaServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_merge_recursive;
use function basename;

final class UploadHandler implements RequestHandlerInterface
{
    public function __construct(
        private AdminUploadFileFilter $inputFilter,
        private MediaServiceInterface $mediaService
    ) {
        $this->inputFilter  = $inputFilter;
        $this->mediaService = $mediaService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = array_merge_recursive(
            $request->getParsedBody(),
            $request->getUploadedFiles(),
        );

        $this->inputFilter->setData($body);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->inputFilter->getMessages(),
            ], 422);
        }

        $file     = $this->inputFilter->getValues()['file'];
        $filename = basename($file->getStream()->getMetadata()['uri']);

        $this->mediaService->putFile($file);

        return new JsonResponse([
            'data' => [
                'filename' => $filename,
            ],
        ]);
    }
}
