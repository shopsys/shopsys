<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Symfony\Component\Security\Core\User\UserInterface;

class FrontendApiUser implements UserInterface
{
    public const CLAIM_UUID = 'uuid';
    public const CLAIM_FULL_NAME = 'fullName';
    public const CLAIM_EMAIL = 'email';
    public const CLAIM_ROLES = 'roles';
    public const CLAIM_SECRET_CHAIN = 'secretChain';
    public const CLAIM_DEVICE_ID = 'deviceId';

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
     * @var string
     */
    protected $deviceId;

    /**
     * @param string $uuid
     * @param string $fullName
     * @param string $email
     * @param string $deviceId
     * @param string[] $roles
     */
    public function __construct(string $uuid, string $fullName, string $email, string $deviceId, array $roles)
    {
        $this->uuid = $uuid;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->roles = $roles;
        $this->deviceId = $deviceId;
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

    /**
     * @return string
     */
    public function getUserIdentifier(): string
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

    /**
     * @return string
     */
    public function getDeviceId(): string
    {
        return $this->deviceId;
    }
}
