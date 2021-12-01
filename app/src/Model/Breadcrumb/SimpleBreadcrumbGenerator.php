<?php

declare(strict_types=1);

namespace App\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Model\Breadcrumb\SimpleBreadcrumbGenerator as BaseSimpleBreadcrumbGenerator;

class SimpleBreadcrumbGenerator extends BaseSimpleBreadcrumbGenerator
{
    /**
     * @return array<string, string>
     */
    protected function getTranslatedBreadcrumbsByRouteNames(): array
    {
        return [
            'front_customer_edit' => t('Edit data'),
            'front_customer_orders' => t('Orders'),
            'front_registration_reset_password' => t('Forgotten password'),
            'front_customer_order_detail_registered' => t('Order detail'),
            'front_customer_order_detail_unregistered' => t('Order detail'),
            'front_login' => t('Login'),
            'front_product_search' => t('Search [noun]'),
            'front_registration_register' => t('Registration'),
            'front_brand_list' => t('Brand overview'),
            'front_contact' => t('Contact'),
        ];
    }
}
