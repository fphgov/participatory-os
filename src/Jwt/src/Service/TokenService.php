<?php

declare(strict_types=1);

namespace Jwt\Service;

use App\Entity\BlacklistedTokens;
use App\Entity\UserInterface;
use App\Repository\BlacklistedTokensRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token as TokenInterface;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;

final class TokenService implements TokenServiceInterface
{
    private BlacklistedTokensRepository $blacklistedTokensRepository;

    public function __construct(
        private readonly array                  $config,
        private readonly EntityManagerInterface $em,
    ) {
        $this->blacklistedTokensRepository = $this->em->getRepository(BlacklistedTokens::class);
    }

    public function generateToken(array $claim = []): TokenInterface
    {
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->config['auth']['secret'])
        );

        $time = new DateTimeImmutable();

        // $usedAfter = $time->modify('+' . $this->config['nbf'] . ' minute');
        $expiresAt = $time->modify('+' . $this->config['exp'] . ' hour');

        return $configuration->builder()
            ->issuedBy($this->config['iss']) // Configures the issuer (iss claim)
            ->permittedFor($this->config['aud']) // Configures the issuer (iss claim)
            ->identifiedBy($this->config['jti']) // Configures the audience (aud claim)
            ->issuedAt($time) // Configures the time that the token was issued (iat claim)
            // ->canOnlyBeUsedAfter($usedAfter) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($expiresAt) // Configures the expiration time of the token (exp claim)
            ->withClaim('user', $claim)
            ->getToken($configuration->signer(), $configuration->signingKey());
    }

    public function createTokenWithUserData(UserInterface $user): TokenInterface
    {
        $userData = [
            'username'  => $user->getUsername(),
            'firstname' => $user->getFirstname(),
            'lastname'  => $user->getLastname(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole(),
        ];

        return $this->generateToken($userData);
    }

    public function invalidateToken(string $token): bool
    {
        try {
            $blacklistedToken = new BlacklistedTokens();
            $blacklistedToken->setToken($token);
            $blacklistedToken->setCreatedAt(new DateTime());

            $this->em->persist($blacklistedToken);
            $this->em->flush();
        } catch (Exception) {
            return false;
        }

        return true;
    }

    public function isTokenBlacklisted(string $token): bool
    {
        $blacklistedToken = $this->blacklistedTokensRepository->findOneBy(['token' => $token]);

        if ($blacklistedToken) {
            return true;
        }
        return false;
    }
}
