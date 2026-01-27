<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;

class OrderAdvancedSearchFilterTranslation extends AdvancedSearchFilterTranslation
{
    public function __construct()
    {
        parent::__construct();

        $this->addFilterTranslation(Filter\OrderNumberFilter::NAME, t('Order number'));
        $this->addFilterTranslation(Filter\OrderCreateDateFilter::NAME, t('Created on'));
        $this->addFilterTranslation(Filter\OrderPriceFilterWithVatFilter::NAME, t('Price including VAT'));
        $this->addFilterTranslation(Filter\OrderStatusFilter::NAME, t('Status of order'));
        $this->addFilterTranslation(Filter\OrderProductFilter::NAME, t('Product in order'));
        $this->addFilterTranslation(Filter\OrderPhoneNumberFilter::NAME, t('Customer phone number'));
        $this->addFilterTranslation(Filter\OrderStreetFilter::NAME, t('Customer street'));
        $this->addFilterTranslation(Filter\OrderNameFilter::NAME, t('Customer name'));
        $this->addFilterTranslation(Filter\OrderLastNameFilter::NAME, t('Customer last name'));
        $this->addFilterTranslation(Filter\OrderEmailFilter::NAME, t('Customer email address'));
        $this->addFilterTranslation(Filter\OrderCityFilter::NAME, t('Customer city'));
        $this->addFilterTranslation(Filter\OrderCustomerIdFilter::NAME, t('Customer ID'));
        $this->addFilterTranslation(Filter\OrderWithdrawalRequestFilter::NAME, t('Withdrawal Request'));
    }
}
