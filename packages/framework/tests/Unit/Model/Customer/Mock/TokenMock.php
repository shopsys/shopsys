<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer\Mock;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenMock implements TokenInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function __construct(private readonly CustomerUser $customerUser)
    {
    }

    public function serialize()
    {
        return null;
    }

    /**
     * @param string $data
     */
    public function unserialize(string $data)
    {
    }

    public function __toString(): string
    {
        return '';
    }

    /**
     * @return array
     */
    public function getRoleNames(): array
    {
        return [];
    }

    public function getCredentials()
    {
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->customerUser;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
    }

    public function isAuthenticated()
    {
        return true;
    }

    /**
     * @param bool $isAuthenticated
     */
    public function setAuthenticated(bool $isAuthenticated)
    {
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return true;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name): mixed
    {
        throw new InvalidArgumentException();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void
    {
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
    }

    public function getUsername()
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
