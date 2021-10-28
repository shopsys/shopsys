<?php

declare(strict_types=1);

namespace App\Model\Security;

use Shopsys\FrameworkBundle\Model\Security\Roles as BaseRoles;

class Roles extends BaseRoles
{
    public const ROLE_ALL = 'ROLE_ALL';

    public const ROLE_ORDER_FULL = 'ROLE_ORDER_FULL';
    public const ROLE_ORDER_VIEW = 'ROLE_ORDER_VIEW';

    public const ROLE_CUSTOMER_FULL = 'ROLE_CUSTOMER_FULL';
    public const ROLE_CUSTOMER_VIEW = 'ROLE_CUSTOMER_VIEW';

    public const ROLE_NEWSLETTER_FULL = 'ROLE_NEWSLETTER_FULL';
    public const ROLE_NEWSLETTER_VIEW = 'ROLE_NEWSLETTER_VIEW';

    /**
     * @return array<string, string>
     */
    public static function getAvailableAdministratorRoles(): array
    {
        return [
            self::ROLE_ALL => t('All'),
            self::ROLE_ORDER_FULL => t('Orders - full'),
            self::ROLE_ORDER_VIEW => t('Orders - view'),
            self::ROLE_CUSTOMER_FULL => t('Customers - full'),
            self::ROLE_CUSTOMER_VIEW => t('Customers - view'),
            self::ROLE_NEWSLETTER_FULL => t('Newsletter - full'),
            self::ROLE_NEWSLETTER_VIEW => t('Newsletter - view'),
        ];
    }
}
