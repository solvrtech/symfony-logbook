<?php

namespace Solvrtech\Logbook\Security;

use Solvrtech\Logbook\Exception\LogbookSecurityException;
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
        return $request->headers->has('x-logbook-key') ?:
            throw new LogbookSecurityException();
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(Request $request): SelfValidatingPassport
    {
        $givenKey = $request->headers->get('x-logbook-key');

        if ($this->logbookKey !== $givenKey) {
            throw new LogbookSecurityException();
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $givenKey
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
