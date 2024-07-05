<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Hybridauth\User\Profile;
use Overblog\GraphQLBundle\Definition\Argument;
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
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData
     */
    public function createWithArgument(Argument $argument): RegistrationData
    {
        $input = $argument['input'];

        $domainId = $this->domain->getId();
        $registrationData = $this->createForDomainId($domainId);

        foreach ($input as $key => $value) {
            if (property_exists(get_class($registrationData), $key)) {
                $registrationData->{$key} = $value;
            }
        }

        $registrationData->country = $this->countryFacade->findByCode($input['country']);

        return $registrationData;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData
     */
    public function createForDomainId(int $domainId): RegistrationData
    {
        $registrationData = $this->create();
        $registrationData->domainId = $domainId;

        return $registrationData;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData
     */
    public function create(): RegistrationData
    {
        return new RegistrationData();
    }

    /**
     * @param \Hybridauth\User\Profile $profile
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData
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
