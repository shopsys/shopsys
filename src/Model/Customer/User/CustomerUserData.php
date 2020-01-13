<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseUserData;

class CustomerUserData extends BaseUserData
{
    /**
     * @var string|null
     */
    public $gender;

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
     * @var bool|null
     */
    public $companyCustomer = false;

    /**
     * @var bool|null
     */
    public $newsletterSubscription = false;

    public function __construct()
    {
        parent::__construct();
    }
}
