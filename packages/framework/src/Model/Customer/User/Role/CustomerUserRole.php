<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

class CustomerUserRole
{
    public const string ROLE_API_ALL = 'ROLE_API_ALL';
    public const string ROLE_API_CUSTOMER_SELF_MANAGE = 'ROLE_API_CUSTOMER_SELF_MANAGE';
    public const string ROLE_API_CUSTOMER_SEES_PRICES = 'ROLE_API_CUSTOMER_SEES_PRICES';
    public const string ROLE_API_CART_AND_ORDER_CREATION = 'ROLE_API_CART_AND_ORDER_CREATION';
    public const string ROLE_API_COMPANY_ORDERS_VIEW = 'ROLE_API_COMPANY_ORDERS_VIEW';
    public const string ROLE_API_COMPLAINT_CREATION = 'ROLE_API_COMPLAINT_CREATION';
    public const string ROLE_API_COMPANY_COMPLAINTS_VIEW = 'ROLE_API_COMPANY_COMPLAINTS_VIEW';

    /**
     * @return array<string, string>
     */
    public function getAvailableRoles(): array
    {
        return [
            t('B2B data and user management') => self::ROLE_API_ALL,
            t('Customer self manage') => self::ROLE_API_CUSTOMER_SELF_MANAGE,
            t('Customer sees prices') => self::ROLE_API_CUSTOMER_SEES_PRICES,
            t('Cart manipulation and order creation') => self::ROLE_API_CART_AND_ORDER_CREATION,
            t('Access to all the orders created under the user\'s company') => self::ROLE_API_COMPANY_ORDERS_VIEW,
            t('Complaint creation') => self::ROLE_API_COMPLAINT_CREATION,
            t('Access to all the complaints created under the user\'s company') => self::ROLE_API_COMPANY_COMPLAINTS_VIEW,
        ];
    }
}
