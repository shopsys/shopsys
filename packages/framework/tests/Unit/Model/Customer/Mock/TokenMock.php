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

    public function __toString()
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

    public function getUser()
    {
        return $this->customerUser;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
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

    public function eraseCredentials()
    {
    }

    public function getAttributes()
    {
        return [];
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
    }

    /**
     * @param string $name
     */
    public function hasAttribute(string $name)
    {
        return true;
    }

    /**
     * @param string $name
     */
    public function getAttribute(string $name)
    {
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute(string $name, $value)
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
