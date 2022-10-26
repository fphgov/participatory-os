<?php

declare(strict_types=1);

namespace App\Handler\Tools;

use App\InputFilter\AdminUploadFileFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_merge_recursive;
use function basename;

final class UploadHandler implements RequestHandlerInterface
{
    /** @var AdminUploadFileFilter */
    private $inputFilter;

    public function __construct(
        AdminUploadFileFilter $inputFilter
    ) {
        $this->inputFilter = $inputFilter;
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

        $file = $this->inputFilter->getValues()['file'];

        return new JsonResponse([
            'data' => [
                'filename' => basename($file->getStream()->getMetadata()['uri']),
            ],
        ]);
    }
}
