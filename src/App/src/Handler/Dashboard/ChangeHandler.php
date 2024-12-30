<?php

declare(strict_types=1);

namespace App\Handler\Dashboard;

use App\Service\SettingServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ChangeHandler implements RequestHandlerInterface
{
    public function __construct(
        private SettingServiceInterface $settingService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        try {
            $setting = $this->settingService->modifySetting($body);
        } catch (Exception $e) {
            return new JsonResponse([
                'errors' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse($setting);
    }
}
