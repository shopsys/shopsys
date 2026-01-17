<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer\Mock;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenMock implements TokenInterface
{
    public function __construct(private readonly CustomerUser $customerUser)
    {
    }

    public function serialize()
    {
        return null;
    }

    public function unserialize(string $data)
    {
    }

    public function __toString(): string
    {
        return '';
    }

    #[Override]
    public function getRoleNames(): array
    {
        return [];
    }

    public function getCredentials()
    {
    }

    #[Override]
    public function getUser(): ?UserInterface
    {
        return $this->customerUser;
    }

    /**
     * @param mixed $user
     */
    #[Override]
    public function setUser($user): void
    {
    }

    public function isAuthenticated()
    {
        return true;
    }

    public function setAuthenticated(bool $isAuthenticated)
    {
    }

    #[Override]
    public function eraseCredentials(): void
    {
    }

    #[Override]
    public function getAttributes(): array
    {
        return [];
    }

    #[Override]
    public function setAttributes(array $attributes): void
    {
    }

    #[Override]
    public function hasAttribute(string $name): bool
    {
        return true;
    }

    #[Override]
    public function getAttribute(string $name): mixed
    {
        throw new InvalidArgumentException();
    }

    /**
     * @param mixed $value
     */
    #[Override]
    public function setAttribute(string $name, $value): void
    {
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    public function getUsername()
    {
        return '';
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->customerUser->getEmail();
    }
}
