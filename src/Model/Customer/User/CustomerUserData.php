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
     * @var bool|null
     */
    public $newsletterSubscription = false;

    public function __construct()
    {
        parent::__construct();
    }
}
