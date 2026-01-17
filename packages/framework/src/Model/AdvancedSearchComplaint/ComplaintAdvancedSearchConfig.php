<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig;

class ComplaintAdvancedSearchConfig extends AdvancedSearchConfig
{
    public function __construct(
        Filter\ComplaintNumberFilter $complaintNumberFilter,
        Filter\OrderNumberFilter $orderNumberFilter,
        Filter\ComplaintCreateDateFilter $complaintCreateDateFilter,
        Filter\ComplaintDeliveryLastNameFilter $complaintDeliveryLastNameFilter,
        Filter\ComplaintDeliveryStreetFilter $complaintDeliveryStreetFilter,
        Filter\ComplaintDeliveryCityFilter $complaintDeliveryCityFilter,
        Filter\ComplaintDeliveryPhoneNumberFilter $complaintDeliveryPhoneNumberFilter,
    ) {
        parent::__construct();

        $this->registerFilter($complaintNumberFilter);
        $this->registerFilter($orderNumberFilter);
        $this->registerFilter($complaintCreateDateFilter);
        $this->registerFilter($complaintDeliveryLastNameFilter);
        $this->registerFilter($complaintDeliveryStreetFilter);
        $this->registerFilter($complaintDeliveryCityFilter);
        $this->registerFilter($complaintDeliveryPhoneNumberFilter);
    }
}
