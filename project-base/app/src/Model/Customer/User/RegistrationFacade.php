<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationFacade as BaseRegistrationFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method __construct(\App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method \App\Model\Customer\User\CustomerUser register(\App\Model\Customer\User\RegistrationData $registrationData)
 * @method \App\Model\Customer\User\CustomerUserUpdateData mapRegistrationDataToCustomerUserUpdateData(\App\Model\Customer\User\CustomerUser $customerUser, \App\Model\Customer\User\RegistrationData $registrationData)
 */
class RegistrationFacade extends BaseRegistrationFacade
{
}
