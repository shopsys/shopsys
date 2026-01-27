<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig;

class ComplaintAdvancedSearchConfig extends AdvancedSearchConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintNumberFilter $complaintNumberFilter
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintOrderNumberFilter $complaintOrderNumberFilter
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintCreateDateFilter $complaintCreateDateFilter
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintDeliveryLastNameFilter $complaintDeliveryLastNameFilter
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintDeliveryStreetFilter $complaintDeliveryStreetFilter
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintDeliveryCityFilter $complaintDeliveryCityFilter
     * @param \Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintDeliveryPhoneNumberFilter $complaintDeliveryPhoneNumberFilter
     */
    public function __construct(
        Filter\ComplaintNumberFilter $complaintNumberFilter,
        Filter\ComplaintOrderNumberFilter $complaintOrderNumberFilter,
        Filter\ComplaintCreateDateFilter $complaintCreateDateFilter,
        Filter\ComplaintDeliveryLastNameFilter $complaintDeliveryLastNameFilter,
        Filter\ComplaintDeliveryStreetFilter $complaintDeliveryStreetFilter,
        Filter\ComplaintDeliveryCityFilter $complaintDeliveryCityFilter,
        Filter\ComplaintDeliveryPhoneNumberFilter $complaintDeliveryPhoneNumberFilter,
    ) {
        parent::__construct();

        $this->registerFilter($complaintNumberFilter);
        $this->registerFilter($complaintOrderNumberFilter);
        $this->registerFilter($complaintCreateDateFilter);
        $this->registerFilter($complaintDeliveryLastNameFilter);
        $this->registerFilter($complaintDeliveryStreetFilter);
        $this->registerFilter($complaintDeliveryCityFilter);
        $this->registerFilter($complaintDeliveryPhoneNumberFilter);
    }
}
