<?php

namespace App\Security;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private ApiTokenRepository $apiTokenRepository)
    {
    } 

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $apiToken = $this->apiTokenRepository->findOneBy([
            'token' => $accessToken,
        ]);

        if (!$apiToken) {
            throw new BadCredentialsException('token not found');
        }

        if (!$apiToken->isValid()) {
            throw new CustomUserMessageAuthenticationException('token expired');
        }

        return new UserBadge($apiToken->getOwnedBy()->getUserIdentifier());
    }
}