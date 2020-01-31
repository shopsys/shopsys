<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

class RegistrationData
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
    public $companyNumberWithVat;

    /**
     * @var bool|null
     */
    public $newsletterSubscription = false;

    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \DateTime|null
     */
    public $createdAt;

    /**
     * @var string|null
     */
    public $telephone;

    public function __construct()
    {
    }
}
