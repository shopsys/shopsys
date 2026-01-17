<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Override;
use Symfony\Component\Security\Core\User\UserInterface;

class FrontendApiUser implements UserInterface
{
    public const CLAIM_UUID = 'uuid';
    public const CLAIM_FULL_NAME = 'fullName';
    public const CLAIM_EMAIL = 'email';
    public const CLAIM_ROLES = 'roles';
    public const CLAIM_SECRET_CHAIN = 'secretChain';
    public const CLAIM_DEVICE_ID = 'deviceId';
    public const CLAIM_ADMINISTRATOR_UUID = 'administratorUuid';

    /**
     * @param string[] $roles
     */
    public function __construct(
        protected readonly string $uuid,
        protected readonly string $fullName,
        protected readonly string $email,
        protected readonly string $deviceId,
        protected readonly array $roles,
        protected readonly ?string $administratorUuid,
    ) {
    }

    #[Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): string
    {
        return '';
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[Override]
    public function eraseCredentials(): void
    {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getAdministratorUuid(): ?string
    {
        return $this->administratorUuid;
    }
}
