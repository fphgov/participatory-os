<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Middleware\UserMiddleware;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PersonalChangeHandler implements RequestHandlerInterface
{
    /** @var UserServiceInterface **/
    private $userService;

    /** @var InputFilterInterface **/
    private $personalFilter;

    public function __construct(
        UserServiceInterface $userService,
        InputFilterInterface $personalFilter
    ) {
        $this->userService     = $userService;
        $this->personalFilter  = $personalFilter;
    }

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

        $this->personalFilter->setData($body);

        if (!$this->personalFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->personalFilter->getMessages(),
            ], 422);
        }

        $this->userService->changePersonalData($user, $this->personalFilter->getValues());

        return new JsonResponse([
            'message' => 'Sikeres személyes adat módosítás',
        ]);
    }
}
