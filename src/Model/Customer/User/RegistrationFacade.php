<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use App\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;

class RegistrationFacade implements RegistrationFacadeInterface
{
    /**
     * @var \App\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private $customerUserUpdateDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @param \App\Model\Country\CountryFacade $countryFacade
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        CountryFacade $countryFacade,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        CustomerUserFacade $customerUserFacade
    ) {
        $this->countryFacade = $countryFacade;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function register(RegistrationData $registrationData): CustomerUser
    {
        $country = $this->countryFacade->getCountryOnCurrentDomain();

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromRegistrationData($registrationData);
        $customerUserUpdateData->billingAddressData->country = $country;

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->create($customerUserUpdateData);
        return $customerUser;
    }

    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function registerCompany(RegistrationData $registrationData): CustomerUser
    {
        $registrationData->companyCustomer = true;
        $registrationData->firstName = $registrationData->companyName;
        $registrationData->lastName = $registrationData->companyName;
        $registrationData->gender = '';
        return $this->register($registrationData);
    }
}
