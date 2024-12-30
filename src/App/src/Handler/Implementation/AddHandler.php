<?php

declare(strict_types=1);

namespace App\Handler\Implementation;

use App\InputFilter\ImplementationFilter;
use App\Middleware\UserMiddleware;
use App\Service\ImplementationService;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_merge_recursive;

final class AddHandler implements RequestHandlerInterface
{
    public function __construct(
        private ImplementationService $implementationService,
        private ImplementationFilter $inputfilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        $body = array_merge_recursive(
            $request->getParsedBody(),
            $request->getUploadedFiles(),
        );

        $this->inputfilter->setData($body);

        if (! $this->inputfilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->inputfilter->getMessages(),
            ], 422);
        }

        $filteredParams = $this->inputfilter->getValues();

        try {
            $this->implementationService->addImplementation($user, $filteredParams);
        } catch (Exception $e) {
            return new JsonResponse([
                'errors' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Sikeres a megvalósítás hozzáadása',
        ]);
    }
}
