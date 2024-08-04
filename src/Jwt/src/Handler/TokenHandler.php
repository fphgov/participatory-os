<?php

declare(strict_types=1);

namespace Jwt\Handler;

use App\Entity\User;
use App\Entity\UserInterface;
use App\Model\PBKDF2Password;
use App\Service\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Lcobucci\JWT\Token as TokenInterface;
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

        if (! isset($postBody['email']) && !isset($postBody['type'])) {
            return $this->badAuthentication();
        }

        if (!isset($postBody['password']) && $postBody['type'] === "password") {
            return $this->badAuthentication();
        }

        $user = $userRepository->findOneBy(['email' => strtolower($postBody['email'])]);

        if (! $user && $postBody['type'] === "login") {
            return $this->sendNotificationNoHasAccount($postBody['email']);
        }

        if (! $user) {
            return $this->badAuthentication();
        }

        if (! $user->getActive()) {
            return $this->badAuthentication();
        }

        if (
            $routeResult->getMatchedRouteName() === 'admin.api.login' &&
            in_array($user->getRole(), ['guest', 'user'], true)
        ) {
            return $this->badAuthentication();
        }

        if ($postBody['type'] === "login") {
            return $this->loginWithMagicLink($user);
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

    private function loginWithMagicLink(UserInterface $user) {
        try {
            $this->userService->accountLoginWithMagicLink($user);
        } catch (\Exception $e) {
            return $this->badAuthentication();
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
}
