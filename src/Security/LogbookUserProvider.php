<?php

namespace Solvrtech\Logbook\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LogbookUserProvider implements UserProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof LogbookUser) {
            throw new UnsupportedUserException(
                sprintf('Invalid user class "%s".', get_class($user))
            );
        }

        throw new UserNotFoundException();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass(string $class): bool
    {
        return $class === LogbookUser::class ||
            is_subclass_of($class, LogbookUser::class);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return (new LogbookUser)->setUserIdentifier($identifier);
    }
}
