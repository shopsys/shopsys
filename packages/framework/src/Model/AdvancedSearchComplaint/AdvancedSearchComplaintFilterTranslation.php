<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;

class AdvancedSearchComplaintFilterTranslation extends AdvancedSearchFilterTranslation
{
    public function __construct()
    {
        parent::__construct();

        $this->addFilterTranslation(Filter\ComplaintNumberFilter::NAME, t('Complaint number'));
        $this->addFilterTranslation(Filter\OrderNumberFilter::NAME, t('Order number'));
        $this->addFilterTranslation(Filter\ComplaintCreateDateFilter::NAME, t('Created on'));
        $this->addFilterTranslation(Filter\ComplaintDeliveryLastNameFilter::NAME, t('Delivery last name'));
        $this->addFilterTranslation(Filter\ComplaintDeliveryStreetFilter::NAME, t('Delivery street'));
        $this->addFilterTranslation(Filter\ComplaintDeliveryCityFilter::NAME, t('Delivery city'));
        $this->addFilterTranslation(Filter\ComplaintDeliveryPhoneNumberFilter::NAME, t('Delivery phone number'));
    }
}
