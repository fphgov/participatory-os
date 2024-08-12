<?php

declare(strict_types=1);

namespace Jwt\Handler;

use App\Entity\User;
use App\Entity\UserInterface;
use App\Model\PBKDF2Password;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Jwt\Service\TokenServiceInterface;

use function in_array;
use function strtolower;

class TokenHandler implements RequestHandlerInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        private UserServiceInterface $userService,
        private TokenServiceInterface $tokenService,
        private array $config
    ) {
        $this->em           = $em;
        $this->userService  = $userService;
        $this->tokenService = $tokenService;
        $this->config       = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $postBody    = $request->getParsedBody();
        $routeResult = $request->getAttribute(RouteResult::class);

        $userRepository = $this->em->getRepository(User::class);

        if (
            ($postBody['type'] === "authentication" || $postBody['type'] === "registration") &&
            (
                !isset($postBody['privacy']) ||
                !isset($postBody['liveInCity']) ||
                $postBody['privacy'] !== 'on' ||
                $postBody['liveInCity'] !== 'on'
            )
        ) {
            return $this->badRequest();
        }

        if (!isset($postBody['email']) && !isset($postBody['type'])) {
            return $this->badRequest();
        }

        if (!isset($postBody['password']) && $postBody['type'] === "password") {
            return $this->badAuthentication();
        }

        $user = $userRepository->findOneBy(['email' => strtolower($postBody['email'])]);

        if ($postBody['type'] === "authentication" || $postBody['type'] === "registration") {
            return $this->registrationAndLoginWithMagicLink(
                $postBody['email'],
                $postBody['prize'],
                (isset($postBody['newsletter']) && $postBody['newsletter'] === 'on'),
                $user,
                $postBody['pathname'] ?? null,
            );
        }

        if (!$user && $postBody['type'] === "login") {
            return $this->sendNotificationNoHasAccount($postBody['email']);
        }

        if (!$user) {
            return $this->badAuthentication();
        }

        if (!$user->getActive()) {
            return $this->badAuthentication();
        }

        if (
            $routeResult->getMatchedRouteName() === 'admin.api.login' &&
            in_array($user->getRole(), ['guest', 'user'], true)
        ) {
            return $this->badAuthentication();
        }

        if ($postBody['type'] === "login") {
            return $this->loginWithMagicLink($user, $postBody['pathname'] ?? null);
        }

        return $this->loginWithPassword($user, $postBody['password']);
    }

    private function loginWithPassword(UserInterface $user, string $password)
    {
        $passwordModel = new PBKDF2Password($user->getPassword(), PBKDF2Password::PW_REPRESENTATION_STORABLE);

        if (!$passwordModel->verify($password)) {
            return $this->badAuthentication();
        }

        $token = $this->tokenService->createTokenWithUserData($user);

        return new JsonResponse([
            'message' => 'Sikeres authentikáció',
            'token'   => $token->toString(),
        ], 200);
    }

    private function sendNotificationNoHasAccount(string $email) {
        try {
            $this->userService->accountLoginNoHasAccount($email);
        } catch (\Exception $e) {
            return $this->badAuthentication();
        }

        return new JsonResponse([
            'message' => 'Az általad megadott e-mail címre elküldtünk egy levelet a további teendőkkel kapcsolatban!',
            'token'   => null,
        ], 200);
    }

    private function loginWithMagicLink(UserInterface $user, ?string $pathname = null) {
        try {
            $this->userService->accountLoginWithMagicLink($user, $pathname);
        } catch (Exception $e) {
            return $this->badAuthentication();
        }

        return new JsonResponse([
            'message' => 'Az általad megadott e-mail címre elküldtünk egy levelet a további teendőkkel kapcsolatban!',
            'token'   => null,
        ], 200);
    }

    private function registrationAndLoginWithMagicLink($email, $prize, ?bool $newsletter = false, ?UserInterface $user = null, ?string $pathname = null) {
        try {
            if (!$user) {
                $user = $this->userService->registration([
                    'password' => '',
                    'birthyear' => null,
                    'postal_code' => null,
                    'postal_code_type' => null,
                    'live_in_city' => true,
                    'hear_about' => '',
                    'privacy' => true,
                    'reminder_email' => false,
                    'prize' => $prize,
                    'firstname' => '',
                    'lastname' => '',
                    'email' => $email,
                ], false);
            }

            $this->userService->accountLoginWithMagicLink($user, $pathname);
        } catch (Exception $e) {
            error_log($e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return $this->badAuthentication();
        }

        if ($newsletter) {
            try {
                $this->userService->newsletterActivateSimple($user);
            } catch (Exception $e) {}
        }

        return new JsonResponse([
            'message' => 'Az általad megadott e-mail címre elküldtünk egy levelet a további teendőkkel kapcsolatban!',
            'token'   => null,
        ], 200);
    }

    private function badAuthentication(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Hibás bejelentkezési adatok vagy inaktív fiók. Próbálj jelszó emlékeztetőt kérni, ha nem tudsz belépni.',
        ], 400);
    }

    private function badRequest(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Kérlek add meg az összes csillaggal megjelölt mezőt.',
        ], 400);
    }
}
