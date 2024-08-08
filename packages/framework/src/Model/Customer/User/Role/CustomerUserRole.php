<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

class CustomerUserRole
{
    public const ROLE_API_LOGGED_CUSTOMER = 'ROLE_API_LOGGED_CUSTOMER';
    public const ROLE_API_ALL = 'ROLE_API_ALL';
    public const ROLE_API_CUSTOMER_SELF_MANAGE = 'ROLE_API_CUSTOMER_SELF_MANAGE';

    /**
     * @return array<string, string>
     */
    public function getAvailableRoles(): array
    {
        return [
            t('B2B data and user management') => self::ROLE_API_ALL,
            t('Customer self manage') => self::ROLE_API_CUSTOMER_SELF_MANAGE,
        ];
    }
}
