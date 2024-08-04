<?php

declare(strict_types=1);

namespace Jwt\Service;

use App\Entity\UserInterface;
use Lcobucci\JWT\Token as TokenInterface;

interface TokenServiceInterface
{
    public function generateToken(array $claim = []): TokenInterface;

    public function createTokenWithUserData(UserInterface $user): TokenInterface;
}
