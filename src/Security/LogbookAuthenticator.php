<?php

namespace Solvrtech\Symfony\Logbook\Security;

use Solvrtech\Symfony\Logbook\Exception\LogbookSecurityException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LogbookAuthenticator extends AbstractAuthenticator
{
    private string $logbookKey;

    public function __construct(string $logbookKey)
    {
        $this->logbookKey = $logbookKey;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('logbook-key') &&
            $request->getPathInfo() === '/logbook-health' ?:
            throw new LogbookSecurityException();
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(Request $request): SelfValidatingPassport
    {
        $credentials = $request->headers->get('logbook-key');

        if ($credentials !== $this->logbookKey) {
            throw new LogbookSecurityException();
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $credentials
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new LogbookSecurityException();
    }
}
