<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

class CustomerUserData
{
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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup|null
     */
    public $pricingGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative|null
     */
    public $salesRepresentative;

    /**
     * @var \DateTime|null
     */
    public $createdAt;

    /**
     * @var string|null
     */
    public $telephone;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer|null
     */
    public $customer = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public $defaultDeliveryAddress;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var bool
     */
    public $newsletterSubscription = false;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public $roleGroup;

    /**
     * @var bool
     */
    public $sendRegistrationMail;

    public function __construct()
    {
        $this->sendRegistrationMail = false;
    }
}
