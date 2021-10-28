<?php

declare(strict_types=1);

namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const ROLE_ALL = 'ROLE_ALL';

    public const ROLE_ORDER_FULL = 'ROLE_ORDER_FULL';
    public const ROLE_ORDER_VIEW = 'ROLE_ORDER_VIEW';

    /**
     * @return array<string, string>
     */
    public static function getAvailableAdministratorRoles(): array
    {
        return [
            self::ROLE_ALL => t('All'),
            self::ROLE_ORDER_FULL => t('Orders - full'),
            self::ROLE_ORDER_VIEW => t('Orders - view'),
        ];
    }
}
