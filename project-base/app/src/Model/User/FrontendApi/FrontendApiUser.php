<?php

declare(strict_types=1);

namespace App\Model\User\FrontendApi;

use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser as BaseFrontendApiUser;

class FrontendApiUser extends BaseFrontendApiUser
{
    public const CLAIM_ADMINISTRATOR_UUID = 'administratorUuid';

    /**
     * @param string $uuid
     * @param string $fullName
     * @param string $email
     * @param string $deviceId
     * @param array $roles
     * @param string|null $administratorUuid
     */
    public function __construct(
        string $uuid,
        string $fullName,
        string $email,
        string $deviceId,
        array $roles,
        private ?string $administratorUuid = null,
    ) {
        parent::__construct($uuid, $fullName, $email, $deviceId, $roles);
    }

    /**
     * @return string|null
     */
    public function getAdministratorUuid(): ?string
    {
        return $this->administratorUuid;
    }
}
