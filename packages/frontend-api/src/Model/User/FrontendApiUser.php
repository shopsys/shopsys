<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Symfony\Component\Security\Core\User\UserInterface;

class FrontendApiUser implements UserInterface
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $fullName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string[]
     */
    protected $roles;

    /**
     * @param string $uuid
     * @param string $fullName
     * @param string $email
     * @param string[] $roles
     */
    public function __construct(string $uuid, string $fullName, string $email, array $roles)
    {
        $this->uuid = $uuid;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }
}
