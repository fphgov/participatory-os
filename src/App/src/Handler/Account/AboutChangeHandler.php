<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Middleware\UserMiddleware;
use App\Service\UserServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AboutChangeHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService,
        private InputFilterInterface $aboutFilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $user = $request->getAttribute(UserMiddleware::class);

        if (! $user) {
            return new JsonResponse([
                'data' => [
                    'unsuccess' => 'No result',
                ],
            ], 404);
        }

        $this->aboutFilter->setData($body);

        if (!$this->aboutFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->aboutFilter->getMessages(),
            ], 422);
        }

        $this->userService->changeAboutData($user, $this->aboutFilter->getValues());

        return new JsonResponse([
            'message' => 'Sikeres adat módosítás',
        ]);
    }
}
