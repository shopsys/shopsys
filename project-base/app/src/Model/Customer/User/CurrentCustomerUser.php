<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser as BaseCurrentCustomerUser;

/**
 * @property \App\Model\Customer\User\CustomerUser[] $customerUserCache
 * @method \App\Model\Customer\User\CustomerUser|null findCurrentCustomerUser()
 * @method __construct(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade)
 */
class CurrentCustomerUser extends BaseCurrentCustomerUser
{
}
