<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\CustomerUserData as BaseUserData;

class CustomerUserData extends BaseUserData
{
    public function __construct()
    {
        parent::__construct();
    }
}
