<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class CustomerData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddress|null
     */
    public $billingAddress;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]
     */
    public $deliveryAddresses;

    /**
     * @var int
     */
    public $domainId;

    public function __construct()
    {
        $this->deliveryAddresses = [];
    }
}
