<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\UserRepository;
use ItkDev\OpenIdConnect\Exception\ItkOpenIdConnectException;
use ItkDev\OpenIdConnectBundle\Exception\InvalidProviderException;
use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use ItkDev\OpenIdConnectBundle\Security\OpenIdLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class OidcAuthenticator extends OpenIdLoginAuthenticator
{
    public function __construct(
        OpenIdConfigurationProviderManager $providerManager,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $router,
        private readonly array $options,
    ) {
        parent::__construct($providerManager);
    }

    public function authenticate(Request $request): Passport
    {
        try {
            // Validate claims
            $claims = $this->validateClaims($request);

            // Extract properties from claims
            $email = $claims['email'] ?? $claims['upn'] ?? null;
            $rolesClaim = $this->options['roles_claim'] ?? 'roles';
            $roles = $claims[$rolesClaim] ?? [];
            // Check if user already exists already or create a new one.
            $user = $this->userRepository->findOneBy(['email' => $email]) ?? new User();

            if (is_array($roles)) {
                $map = (array) ($this->options['role_map'] ?? null);
                $userRoles = array_map(static fn (string $role) => (array) ($map[$role] ?? null), $roles);
                // Flatten and filter out invalid roles.
                $userRoles = array_filter(array_merge(...$userRoles), static fn (string $role) => null !== UserRole::tryFrom($role));
                $user->setRoles($userRoles);
            }

            $user->setEmail($email);
            $this->userRepository->save($user, flush: true);

            return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
        } catch (ItkOpenIdConnectException|InvalidProviderException $exception) {
            throw new CustomUserMessageAuthenticationException($exception->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_default'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('itkdev_openid_connect_login', [
            'providerKey' => 'admin',
        ]));
    }
}
