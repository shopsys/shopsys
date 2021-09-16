<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseUserData;

/**
 * @property \App\Model\Customer\DeliveryAddress|null $defaultDeliveryAddress
 */
class CustomerUserData extends BaseUserData
{
    /**
     * @var bool|null
     */
    public $newsletterSubscription = false;
}
