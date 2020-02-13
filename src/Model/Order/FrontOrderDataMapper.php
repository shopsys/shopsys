<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\FrontOrderData;
use Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper as BaseFrontOrderDataMapper;

/**
 * @method prefillFrontFormData(\App\Model\Order\FrontOrderData $frontOrderData, \App\Model\Customer\User\CustomerUser $customerUser, \App\Model\Order\Order $order)
 * @method prefillTransportAndPaymentFromOrder(\App\Model\Order\FrontOrderData $frontOrderData, \App\Model\Order\Order $order)
 */
class FrontOrderDataMapper extends BaseFrontOrderDataMapper
{
    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    protected function prefillFrontFormDataFromCustomer(FrontOrderData $frontOrderData, CustomerUser $customerUser)
    {
        parent::prefillFrontFormDataFromCustomer($frontOrderData, $customerUser);

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $frontOrderData->gender = $customerUser->getGender();

        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        $frontOrderData->companyNumberWithVat = $billingAddress->getCompanyNumberWithVat();
    }
}
