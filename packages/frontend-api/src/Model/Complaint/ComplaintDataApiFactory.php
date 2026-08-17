<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

class ComplaintDataApiFactory
{
    public function __construct(
        protected readonly ComplaintDataFactory $complaintDataFactory,
        protected readonly CountryFacade $countryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[] $complaintItems
     */
    public function createFromComplaintInputArgument(
        Argument $argument,
        string $number,
        ?Order $order,
        array $complaintItems,
        string $resolution,
        ?string $bankAccountNumber = null,
        ?CustomerUser $customerUser = null,
    ): ComplaintData {
        $input = $argument['input'];

        $complaintData = $this->complaintDataFactory->create();
        $complaintData->number = $number;
        $complaintData->order = $order;
        $complaintData->manualDocumentNumber = $input['manualDocumentNumber'];
        $complaintData->domainId = $order?->getDomainId() ?? $this->domain->getId();
        $complaintData->resolution = $resolution;
        $complaintData->bankAccountNumber = $bankAccountNumber;
        $complaintData->customerUser = $customerUser;
        $complaintData->complaintItems = $complaintItems;

        $complaintData->email = $input['email'];

        $delivery = $input['deliveryAddress'];
        $complaintData->deliveryFirstName = $delivery['firstName'];
        $complaintData->deliveryLastName = $delivery['lastName'];
        $complaintData->deliveryCompanyName = $delivery['companyName'];
        $telephoneInput = $delivery['telephone'] ?? null;
        $complaintData->deliveryTelephone = $telephoneInput ? PhoneData::fromArray($telephoneInput) : null;
        $complaintData->deliveryStreet = $delivery['street'];
        $complaintData->deliveryCity = $delivery['city'];
        $complaintData->deliveryPostcode = $delivery['postcode'];
        $complaintData->deliveryCountry = $this->countryFacade->findByCode($delivery['country']);

        return $complaintData;
    }
}
