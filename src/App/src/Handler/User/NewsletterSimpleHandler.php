<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Service\UserServiceInterface;
use App\Middleware\UserMiddleware;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NewsletterSimpleHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Logger               $audit,
        private readonly UserServiceInterface $userService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);
        $body = $request->getParsedBody();
        $newsletter = $body['newsletter'] ?? '0';
        $subscribe = ($newsletter === '1');

        $this->audit->info('subscribe: ' . $newsletter);

        try {
            $this->userService->newsletterActivateSimple($user, $subscribe);

            return new JsonResponse([
            'message' => $subscribe ? 'Sikeres feliratkozás a hírlevélre' : 'Sikeres leiratkozás a hírlevélről',
        ]);
        } catch (Exception $e) {
            $this->audit->err($e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine());
            return new JsonResponse([
                'message' => 'Váratlan hiba történt',
            ], 500);
        }
    }
}
