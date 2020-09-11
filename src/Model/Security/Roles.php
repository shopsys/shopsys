<?php

declare(strict_types=1);


namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const ROLE_CUSTOMER_CARE = 'ROLE_CUSTOMER_CARE';

    public const AVAILABLE_ADMINISTRATOR_ROLES = [
        self::ROLE_ADMIN => self::ROLE_ADMIN,
        self::ROLE_CUSTOMER_CARE => self::ROLE_CUSTOMER_CARE
    ];
}
