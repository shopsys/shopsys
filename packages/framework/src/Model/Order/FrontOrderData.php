<?php

namespace Shopsys\FrameworkBundle\Model\Order;

class FrontOrderData extends OrderData
{
    /**
     * @var bool
     */
    public $companyCustomer;

    /**
     * @var bool
     */
    public $newsletterSubscription;

    /**
     * @var bool
     */
    public $disallowHeurekaVerifiedByCustomers;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    public $deliveryAddress;

    public function __construct()
    {
        parent::__construct();
        $this->companyCustomer = false;
        $this->newsletterSubscription = false;
        $this->disallowHeurekaVerifiedByCustomers = false;
    }
}
