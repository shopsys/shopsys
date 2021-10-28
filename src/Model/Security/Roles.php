<?php

declare(strict_types=1);

namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const ROLE_ALL = 'ROLE_ALL';

    /**
     * @return array<string, string>
     */
    public static function getAvailableAdministratorRoles(): array
    {
        return [
            self::ROLE_ALL => t('All'),
        ];
    }
}
