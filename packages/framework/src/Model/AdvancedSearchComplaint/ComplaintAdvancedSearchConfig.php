<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint;

use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig;

class ComplaintAdvancedSearchConfig extends AdvancedSearchConfig
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintNumberFilter $complaintNumberFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\OrderNumberFilter $orderNumberFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintCreateDateFilter $complaintCreateDateFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintDeliveryLastNameFilter $complaintDeliveryLastNameFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintDeliveryStreetFilter $complaintDeliveryStreetFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintDeliveryCityFilter $complaintDeliveryCityFilter
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintDeliveryPhoneNumberFilter $complaintDeliveryPhoneNumberFilter
     */
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
