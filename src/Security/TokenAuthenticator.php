<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createAuthenticatedToken(UserInterface $user, string $providerKey): PostAuthenticationGuardToken
    {
        return parent::createAuthenticatedToken($user, $providerKey);
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function getCredentials(Request $request): array
    {
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            $token = null;
        }

        return [
            'token' => $token,
        ];
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        $token = $credentials['token'] ?? '';

        return $this->userRepository->findUserByToken($token);
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        if (empty($user)) {
            return false;
        }

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): void
    {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $data = [
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

}