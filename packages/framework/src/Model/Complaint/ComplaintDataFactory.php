<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Psr\Clock\ClockInterface;

class ComplaintDataFactory
{
    public function __construct(
        protected readonly ComplaintItemDataFactory $complaintItemDataFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function create(): ComplaintData
    {
        $complaintData = $this->createInstance();
        $complaintData->createdAt = $this->clock->now();

        return $complaintData;
    }

    protected function createInstance(): ComplaintData
    {
        return new ComplaintData();
    }

    public function createFromComplaint(Complaint $complaint): ComplaintData
    {
        $complaintData = $this->createInstance();

        $this->fillFromComplaint($complaintData, $complaint);

        return $complaintData;
    }

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
        $complaintData->deliveryTelephone = $complaint->getDeliveryTelephoneData();
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
