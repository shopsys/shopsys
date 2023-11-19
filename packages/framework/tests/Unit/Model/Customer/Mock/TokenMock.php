<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer\Mock;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenMock implements TokenInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function __construct(private readonly CustomerUser $customerUser)
    {
    }

    public function serialize(): ?string
    {
        return null;
    }

    /**
     * @param string $data
     */
    public function unserialize(string $data): void
    {
    }

    public function __toString(): string
    {
        return '';
    }

    /**
     * @return mixed[]
     */
    public function getRoleNames(): array
    {
        return [];
    }

    public function getCredentials(): void
    {
    }

    public function getUser(): ?\Symfony\Component\Security\Core\User\UserInterface
    {
        return $this->customerUser;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    /**
     * @param bool $isAuthenticated
     */
    public function setAuthenticated(bool $isAuthenticated): void
    {
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * @param mixed[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
    }

    /**
     * @param string $name
     */
    public function hasAttribute(string $name): bool
    {
        return true;
    }

    /**
     * @param string $name
     */
    public function getAttribute(string $name): void
    {
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void
    {
    }

    /**
     * @return mixed[]
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * @param mixed[] $data
     */
    public function __unserialize(array $data): void
    {
    }

    public function getUsername(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->customerUser->getEmail();
    }
}
