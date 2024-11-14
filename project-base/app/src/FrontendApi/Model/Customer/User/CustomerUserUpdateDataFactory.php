<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Customer\User;

use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory as BaseCustomerUserUpdateDataFactory;

/**
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromRegistrationData(\Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData $registrationData)
 * @method __construct(\App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory, \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory, \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromCustomerUserWithArgument(\App\Model\Customer\User\CustomerUser $customerUser, \Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createCompanyFromCustomerUserWithArgument(\App\Model\Customer\User\CustomerUser $customerUser, \Overblog\GraphQLBundle\Definition\Argument $argument)
 */
class CustomerUserUpdateDataFactory extends BaseCustomerUserUpdateDataFactory
{
}
