<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Exception\UserNotActiveException;
use App\Exception\UserNotFoundException;
use App\Service\UserServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Log\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ForgotPasswordHandler implements RequestHandlerInterface
{
    private const RES_MESSAGE       = 'Amennyiben a rendszerünkben szerepel a fiók és ez aktív, úgy a megadott e-mailre kiküldtük a jelszó emlékeztetőt. Ha regisztrált fiókod van, mégsem kapsz jelszóemlékeztetőt, lehetséges, hogy másik e-mail címmel regisztráltál vagy régi fiókod törlődött. Kérjük, regisztrálj újra!';
    private const RES_ERROR_MESSAGE = 'Váratlan hiba történt. A problémát rögzítettük és próbáljuk a lehető legrövidebb időn belül javítani.';

    public function __construct(
        private UserServiceInterface $userService,
        private Logger $audit
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody(); // TODO: filter

        try {
            $this->userService->forgotPassword($body['email']);
        } catch (UserNotActiveException $e) {
            return new JsonResponse([
                'message' => self::RES_MESSAGE,
            ]);
        } catch (UserNotFoundException $e) {
            return new JsonResponse([
                'message' => self::RES_MESSAGE,
            ]);
        } catch (Exception $e) {
            $this->audit->err('Forgot account exception', [
                'extra' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'message' => self::RES_ERROR_MESSAGE,
            ], 400);
        }

        return new JsonResponse([
            'message' => self::RES_MESSAGE,
        ]);
    }
}
