<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

class Roles
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_LOGGED_CUSTOMER = 'ROLE_LOGGED_CUSTOMER';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @return string[]
     */
    public static function getMandatoryAdministratorRoles(): array
    {
        return [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN];
    }
}
