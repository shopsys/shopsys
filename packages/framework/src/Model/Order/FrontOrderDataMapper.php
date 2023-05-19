<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class FrontOrderDataMapper
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\FrontOrderData $frontOrderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function prefillFrontFormData(FrontOrderData $frontOrderData, CustomerUser $customerUser, ?Order $order)
    {
        if ($order instanceof Order) {
            $this->prefillTransportAndPaymentFromOrder($frontOrderData, $order);
        }
        $this->prefillFrontFormDataFromCustomer($frontOrderData, $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\FrontOrderData $frontOrderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function prefillTransportAndPaymentFromOrder(FrontOrderData $frontOrderData, Order $order)
    {
        $frontOrderData->transport = $order->getTransport()->isDeleted() ? null : $order->getTransport();
        $frontOrderData->payment = $order->getPayment()->isDeleted() ? null : $order->getPayment();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\FrontOrderData $frontOrderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    protected function prefillFrontFormDataFromCustomer(FrontOrderData $frontOrderData, CustomerUser $customerUser)
    {
        $frontOrderData->firstName = $customerUser->getFirstName();
        $frontOrderData->lastName = $customerUser->getLastName();
        $frontOrderData->email = $customerUser->getEmail();
        $frontOrderData->telephone = $customerUser->getTelephone();
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        $frontOrderData->companyCustomer = $billingAddress->isCompanyCustomer();
        $frontOrderData->companyName = $billingAddress->getCompanyName();
        $frontOrderData->companyNumber = $billingAddress->getCompanyNumber();
        $frontOrderData->companyTaxNumber = $billingAddress->getCompanyTaxNumber();
        $frontOrderData->street = $billingAddress->getStreet();
        $frontOrderData->city = $billingAddress->getCity();
        $frontOrderData->postcode = $billingAddress->getPostcode();
        $frontOrderData->country = $billingAddress->getCountry();
        $frontOrderData->deliveryAddress = $customerUser->getDefaultDeliveryAddress();
    }
}
