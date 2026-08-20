<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private JWTTokenManagerInterface $jwtManager;
    private TokenExtractorInterface $tokenExtractor;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        TokenExtractorInterface $tokenExtractor
    ) {
        $this->jwtManager = $jwtManager;
        $this->tokenExtractor = $tokenExtractor;
    }

    public function supports(\Symfony\Component\HttpFoundation\Request $request): bool
    {
        return true;
    }

    public function getCredentials(\Symfony\Component\HttpFoundation\Request $request)
    {
        $extractor = $this->tokenExtractor->extract($request);
        if ($extractor) {
            return ['token' => $extractor];
        }

        return null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['token'] ?? null;
        if (!$token) {
            return null;
        }

        $jwtUser = $this->jwtManager->decode($token);
        if (!$jwtUser) {
            return null;
        }

        return $userProvider->loadUserByUsername($jwtUser['email']);
    }

    public function checkCredentials($credentials, \Symfony\Component\Security\Core\User\UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(\Symfony\Component\HttpFoundation\Request $request, AuthenticationException $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        return null;
    }

    public function onAuthenticationSuccess(\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\Security\Core\User\UserInterface $user, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        return null;
    }

    public function start(\Symfony\Component\HttpFoundation\Request $request, AuthenticationException $authException = null): \Symfony\Component\HttpFoundation\Response
    {
        return null;
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
