<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class RegistrationDataFactory implements RegistrationDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    protected CountryFacade $countryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        Domain $domain,
        CountryFacade $countryFacade
    ) {
        $this->domain = $domain;
        $this->countryFacade = $countryFacade;
    }

    /**
     * @param int $domainId
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function createForDomainId(int $domainId): RegistrationData
    {
        $registrationData = $this->create();
        $registrationData->domainId = $domainId;
        return $registrationData;
    }

    /**
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function create(): RegistrationData
    {
        return new RegistrationData();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Customer\User\RegistrationData
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
}
