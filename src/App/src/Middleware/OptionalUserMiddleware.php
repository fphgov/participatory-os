<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\UserServiceInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use Firebase\JWT\JWT;

use function str_replace;

class OptionalUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserServiceInterface $userService,
        private array $options
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $authHeader = $request->getHeaderLine('Authorization');

        if (! $authHeader) {
            return $handler->handle(
                $request
                    ->withAttribute(self::class, null)
            );
        }

        $token = str_replace("Bearer ", "", $authHeader);

        $decodeToken = $this->decodeToken($token);

        if (! $decodeToken) {
            return $handler->handle(
                $request
                    ->withAttribute(self::class, null)
            );
        }

        $user = $this->userService->getRepository()->findOneBy([
            'email' => $decodeToken['user']->email,
        ]);

        $ui = new DefaultUser($user->getEmail(), [$user->getRole()]);

        return $handler->handle(
            $request
                ->withAttribute(self::class, $user)
                ->withAttribute(UserInterface::class, $ui)
        );
    }

    private function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                $this->options["secret"],
                (array) $this->options["algorithm"]
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
