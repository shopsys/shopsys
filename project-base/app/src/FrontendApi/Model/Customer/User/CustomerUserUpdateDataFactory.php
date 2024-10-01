<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory as FrameworkCustomerUserUpdateDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory as BaseCustomerUserUpdateDataFactory;

/**
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromRegistrationData(\Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData $registrationData)
 */
class CustomerUserUpdateDataFactory extends BaseCustomerUserUpdateDataFactory
{
    /**
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        FrameworkCustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        BillingAddressDataFactory $billingAddressDataFactory,
        CustomerUserDataFactory $customerUserDataFactory,
        protected readonly CountryFacade $countryFacade,
    ) {
        parent::__construct($customerUserUpdateDataFactory, $billingAddressDataFactory, $customerUserDataFactory);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromCustomerUserWithArgument(
        CustomerUser $customerUser,
        Argument $argument,
    ): CustomerUserUpdateData {
        $input = $argument['input'];

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserData = $customerUserUpdateData->customerUserData;
        $billingAddressData = $customerUserUpdateData->billingAddressData;

        foreach ($input as $key => $value) {
            if (property_exists(get_class($customerUserData), $key)) {
                $customerUserData->{$key} = $value;
            }

            if (property_exists(get_class($billingAddressData), $key)) {
                $billingAddressData->{$key} = $value;
            }

            $billingAddressData->country = $this->countryFacade->findByCode($input['country']);
        }

        return $customerUserUpdateData;
    }
}
