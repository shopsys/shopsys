<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class BillingAddressData
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var bool
     */
    public $companyCustomer;

    /**
     * @var string|null
     */
    public $companyName;

    /**
     * @var string|null
     */
    public $companyNumber;

    /**
     * @var string|null
     */
    public $companyTaxNumber;

    /**
     * @var string|null
     */
    public $street;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public $country;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public $customer;

    /**
     * @var bool
     */
    public $activated;

    public function __construct()
    {
        $this->companyCustomer = false;
        $this->activated = true;
    }
}
