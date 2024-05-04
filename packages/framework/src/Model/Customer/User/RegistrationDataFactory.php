<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Hybridauth\User\Profile;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class RegistrationDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CountryFacade $countryFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationData
     */
    public function createForDomainId(int $domainId): RegistrationData
    {
        $registrationData = $this->create();
        $registrationData->domainId = $domainId;

        return $registrationData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationData
     */
    public function create(): RegistrationData
    {
        return new RegistrationData();
    }

    /**
     * @param \Hybridauth\User\Profile $profile
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationData
     */
    public function createFromSocialNetworkProfile(Profile $profile): RegistrationData
    {
        $registrationData = $this->createForDomainId($this->domain->getId());
        $countries = $this->countryFacade->getAllEnabledOnCurrentDomain();

        $registrationData->firstName = $profile->firstName ?? '';
        $registrationData->lastName = $profile->lastName ?? '';
        $registrationData->email = $profile->email;
        $registrationData->street = '';
        $registrationData->city = '';
        $registrationData->postcode = '';
        $registrationData->country = $countries[0];
        $registrationData->password = HashGenerator::generateStrongPassword();

        return $registrationData;
    }
}
