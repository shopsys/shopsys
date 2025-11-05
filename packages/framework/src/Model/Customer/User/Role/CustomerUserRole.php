<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

class CustomerUserRole
{
    public const string ROLE_API_ALL = 'ROLE_API_ALL';
    public const string ROLE_API_MANAGE_CUSTOMERS = 'ROLE_API_MANAGE_CUSTOMERS';
    public const string ROLE_API_CUSTOMER_SELF_MANAGE = 'ROLE_API_CUSTOMER_SELF_MANAGE';
    public const string ROLE_API_CUSTOMER_SEES_PRICES = 'ROLE_API_CUSTOMER_SEES_PRICES';
    public const string ROLE_API_CART_AND_ORDER_CREATION = 'ROLE_API_CART_AND_ORDER_CREATION';
    public const string ROLE_API_COMPANY_ORDERS_VIEW = 'ROLE_API_COMPANY_ORDERS_VIEW';
    public const string ROLE_API_COMPLAINT_CREATION = 'ROLE_API_COMPLAINT_CREATION';
    public const string ROLE_API_COMPANY_COMPLAINTS_VIEW = 'ROLE_API_COMPANY_COMPLAINTS_VIEW';
}
