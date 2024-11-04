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
use Laminas\Log\Logger;
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
        private EntityManagerInterface $em,
        private UserServiceInterface $userService,
        private TokenServiceInterface $tokenService,
        private Logger $audit,
        private array $config
    ) {
        $this->em           = $em;
        $this->userService  = $userService;
        $this->tokenService = $tokenService;
        $this->audit        = $audit;
        $this->config       = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $postBody    = $request->getParsedBody();
        $routeResult = $request->getAttribute(RouteResult::class);

        $userRepository = $this->em->getRepository(User::class);

        if (
            isset($postBody['type']) &&
            isset($postBody['token']) &&
            $postBody['type'] === "logout" &&
            $routeResult->getMatchedRouteName() === 'app.api.logout'
        ) {
            $user = $userRepository->findOneBy(['hash' => $postBody['token']]);

            $this->audit->err($user);

            if ($user) {
                $user->setHash();
                $this->em->persist($user);
                $this->em->flush();

                return $this->logoutRequestSuccess();
            }
            return $this->logoutRequestFailed();
        }

        if (
            isset($postBody['type']) &&
            in_array($postBody['type'], UserServiceInterface::AUTH_REGISTRATION_TYPES)
            && (
                !isset($postBody['privacy']) ||
                !isset($postBody['liveInCity']) ||
                $postBody['privacy'] !== 'on' ||
                $postBody['liveInCity'] !== 'on'
            )
        ) {
            return $this->badRequest();
        }

        if (
            !isset($postBody['email']) && !isset($postBody['type']) ||
            isset($postBody['email']) && $postBody['email'] === ""
        ) {
            return $this->badRequest();
        }

        if (!isset($postBody['password']) && $postBody['type'] === "password") {
            return $this->badAuthentication();
        }

        $user = $userRepository->findOneBy(['email' => strtolower($postBody['email'])]);

        if (isset($postBody['type']) && in_array($postBody['type'], UserServiceInterface::AUTH_REGISTRATION_TYPES)) {
            return $this->registrationAndLoginWithMagicLink(
                $postBody['type'],
                $postBody['email'],
                (isset($postBody['prize']) && $postBody['prize'] === 'on'),
                (isset($postBody['newsletter']) && $postBody['newsletter'] === 'on'),
                $user,
                $postBody['pathname'] ?? null,
            );
        }

        if (!$user && isset($postBody['type']) && $postBody['type'] === "login") {
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

        if (isset($postBody['type']) && $postBody['type'] === "login") {
            return $this->loginWithMagicLink($user, $postBody['pathname'] ?? null);
        }

        return $this->loginWithPassword($user, $postBody['password']);
    }

    private function loginWithPassword(UserInterface $user, string $password)
    {
        $passwordModel = new PBKDF2Password($user->getPassword(), PBKDF2Password::PW_REPRESENTATION_STORABLE);

        if ($this->userService->isToManyLoginAttempt($user)) {
            return $this->toManyAttempt();
        }

        if (! $passwordModel->verify($password)) {
            $this->userService->addUserLoginAttempt($user, true);

            return $this->badAuthentication();
        }

        $token = $this->tokenService->createTokenWithUserData($user);

        $this->userService->addUserLoginAttempt($user, false);

        return new JsonResponse([
            'message' => 'Sikeres authentikáció',
            'token'   => $token->toString(),
        ], 200);
    }

    private function sendNotificationNoHasAccount(string $email) {
        try {
            $this->userService->accountLoginNoHasAccount($email);
        } catch (\Exception $e) {
            return $this->badAuthentication($e);
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
            return $this->badAuthentication($e);
        }

        return new JsonResponse([
            'message' => 'Az általad megadott e-mail címre elküldtünk egy levelet a további teendőkkel kapcsolatban!',
            'token'   => null,
        ], 200);
    }

    private function registrationAndLoginWithMagicLink(
        string $type,
        string $email,
        bool $prize,
        ?bool $newsletter = false,
        ?UserInterface $user = null,
        ?string $pathname = null
    ) {
        try {
            $isNewAccount = false;

            if (! $user) {
                $isNewAccount = true;

                $user = $this->userService->registration([
                    'password'         => '',
                    'birthyear'        => null,
                    'postal_code'      => null,
                    'postal_code_type' => null,
                    'live_in_city'     => true,
                    'hear_about'       => '',
                    'privacy'          => true,
                    'reminder_email'   => false,
                    'prize'            => $prize,
                    'firstname'        => '',
                    'lastname'         => '',
                    'email'            => $email,
                ], false);
            }

            if ($type === UserServiceInterface::AUTH_AUTHENTICATION) {
                $this->userService->accountLoginWithMagicLinkAuthentication($user, $pathname);
            } else if ($isNewAccount) {
                $this->userService->accountLoginWithMagicLinkIsNewAccount($user, $pathname);
            } else {
                $this->userService->accountLoginWithMagicLink($user, $pathname);
            }
        } catch (Exception $e) {
            return $this->badAuthentication($e);
        }

        if ($newsletter) {
            try {
                $this->userService->newsletterActivateSimple($user, true);
            } catch (Exception $e) {
                $this->audit->err($e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            }
        }

        return new JsonResponse([
            'message' => 'Az általad megadott e-mail címre elküldtünk egy levelet a további teendőkkel kapcsolatban!',
            'token'   => null,
        ], 200);
    }

    private function badAuthentication($e = null): JsonResponse
    {
        if ($e) {
            $this->audit->err($e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
        }

        return new JsonResponse([
            'message' => 'Hibás bejelentkezési adatok vagy inaktív fiók. Próbálj jelszó emlékeztetőt kérni, ha nem tudsz belépni.',
        ], 400);
    }

    private function badRequest(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Kérlek, jelöld be az összes csillaggal megjelölt mezőt.',
        ], 400);
    }

    private function toManyAttempt(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Túl sok sikertelen bejelentkezési kísérlet! Kérlek, várj 15 percet, mielőtt újra próbálkoznál.',
        ], 400);
    }

    private function logoutRequestSuccess(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Sikeres kijelentkezés',
        ], 200);
    }

    private function logoutRequestFailed(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Sikertelen kijelentkezés',
        ], 400);
    }
}
