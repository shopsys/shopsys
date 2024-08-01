<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintStatusEnum;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;

class ComplaintDataApiFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory $complaintDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        protected readonly ComplaintDataFactory $complaintDataFactory,
        protected readonly CountryFacade $countryFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param string $number
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $complaintItems
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    public function createFromComplaintInputArgument(
        Argument $argument,
        string $number,
        Order $order,
        array $complaintItems,
        ?CustomerUser $customerUser = null,
    ): ComplaintData {
        $input = $argument['input'];

        $complaintData = $this->complaintDataFactory->create();
        $complaintData->number = $number;
        $complaintData->order = $order;
        $complaintData->customerUser = $customerUser;
        $complaintData->complaintItems = $complaintItems;

        $delivery = $input['deliveryAddress'];
        $complaintData->deliveryFirstName = $delivery['firstName'];
        $complaintData->deliveryLastName = $delivery['lastName'];
        $complaintData->deliveryCompanyName = $delivery['companyName'];
        $complaintData->deliveryTelephone = $delivery['telephone'];
        $complaintData->deliveryStreet = $delivery['street'];
        $complaintData->deliveryCity = $delivery['city'];
        $complaintData->deliveryPostcode = $delivery['postcode'];
        $complaintData->deliveryCountry = $this->countryFacade->findByCode($delivery['country']);
        $complaintData->status = ComplaintStatusEnum::STATUS_NEW;

        return $complaintData;
    }
}
