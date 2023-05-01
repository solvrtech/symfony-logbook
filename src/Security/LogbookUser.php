<?php

namespace Solvrtech\Symfony\Logbook\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class LogbookUser implements UserInterface
{
    private array $roles = [];
    private ?string $userIdentifier = null;

    /**
     * {@inheritDoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles = []): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }
}
