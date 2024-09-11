<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

class ComplaintDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    public function create(): ComplaintData
    {
        return $this->createInstance();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    protected function createInstance(): ComplaintData
    {
        return new ComplaintData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    public function createFromComplaint(Complaint $complaint): ComplaintData
    {
        $complaintData = $this->createInstance();

        $this->fillFromComplaint($complaintData, $complaint);

        return $complaintData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     */
    protected function fillFromComplaint(
        ComplaintData $complaintData,
        Complaint $complaint,
    ): void {
        $complaintData->uuid = $complaint->getUuid();
        $complaintData->domainId = $complaint->getDomainId();
        $complaintData->number = $complaint->getNumber();
        $complaintData->status = $complaint->getStatus();
        $complaintData->order = $complaint->getOrder();
        $complaintData->customerUser = $complaint->getCustomerUser();
        $complaintData->deliveryFirstName = $complaint->getDeliveryFirstName();
        $complaintData->deliveryLastName = $complaint->getDeliveryLastName();
        $complaintData->deliveryCompanyName = $complaint->getDeliveryCompanyName();
        $complaintData->deliveryTelephone = $complaint->getDeliveryTelephone();
        $complaintData->deliveryStreet = $complaint->getDeliveryStreet();
        $complaintData->deliveryCity = $complaint->getDeliveryCity();
        $complaintData->deliveryPostcode = $complaint->getDeliveryPostcode();
        $complaintData->deliveryCountry = $complaint->getDeliveryCountry();
    }
}
