<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Mail\Header\HeaderName;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function strtolower;

final class RegistrationHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserServiceInterface $userService,
        private InputFilterInterface $userRegistrationFilter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        $this->userRegistrationFilter->setData($body);

        if (! $this->userRegistrationFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->userRegistrationFilter->getMessages(),
            ], 422);
        }

        $email = strtolower($this->userRegistrationFilter->getValues()['email']);

        try {
            HeaderName::assertValid($email);
        } catch (Exception $e) {
            return new JsonResponse([
                'errors' => [
                    'email' => [
                        'format' => 'Nem megfelelő e-mail cím. Kérjük ellenőrizze újra. Ékezetes betűk és a legtöbb speciális karakter nem elfogadható.',
                    ],
                ],
            ], 422);
        }

        try {
            $this->userService->registration($this->userRegistrationFilter->getValues());
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Sikertelen regisztráció',
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Sikeres aktiválás',
        ]);
    }
}
