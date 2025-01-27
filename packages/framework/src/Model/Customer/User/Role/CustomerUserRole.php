<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

class CustomerUserRole
{
    public const string ROLE_API_LOGGED_CUSTOMER = 'ROLE_API_LOGGED_CUSTOMER';
    public const string ROLE_API_ALL = 'ROLE_API_ALL';
    public const string ROLE_API_CUSTOMER_SELF_MANAGE = 'ROLE_API_CUSTOMER_SELF_MANAGE';
    public const string ROLE_API_CUSTOMER_SEES_PRICES = 'ROLE_API_CUSTOMER_SEES_PRICES';
    public const string ROLE_API_ORDER_FULL = 'ROLE_API_ORDER_FULL';
    public const string ROLE_API_ORDER_VIEW = 'ROLE_API_ORDER_VIEW';

    /**
     * @return array<string, string>
     */
    public function getAvailableRoles(): array
    {
        return [
            t('B2B data and user management') => self::ROLE_API_ALL,
            t('Customer self manage') => self::ROLE_API_CUSTOMER_SELF_MANAGE,
            t('Customer sees prices') => self::ROLE_API_CUSTOMER_SEES_PRICES,
            t('Cart manipulation and order creation') => self::ROLE_API_ORDER_FULL,
            t('Information about orders') => self::ROLE_API_ORDER_VIEW,
        ];
    }
}
