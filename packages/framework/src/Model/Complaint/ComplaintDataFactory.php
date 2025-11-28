<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Psr\Clock\ClockInterface;

class ComplaintDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory $complaintItemDataFactory
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly ComplaintItemDataFactory $complaintItemDataFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    public function create(): ComplaintData
    {
        $complaintData = $this->createInstance();
        $complaintData->createdAt = $this->clock->now();

        return $complaintData;
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
        $complaintData->createdAt = $complaint->getCreatedAt();
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
        $complaintData->email = $complaint->getEmail();
        $complaintData->manualDocumentNumber = $complaint->getManualDocumentNumber();
        $complaintData->resolution = $complaint->getResolution();
        $complaintData->bankAccountNumber = $complaint->getBankAccountNumber();

        foreach ($complaint->getItems() as $complaintItem) {
            $complaintItemData = $this->complaintItemDataFactory->createFromComplaintItem($complaintItem);
            $complaintData->complaintItems[$complaintItem->getId()] = $complaintItemData;
        }
    }
}
