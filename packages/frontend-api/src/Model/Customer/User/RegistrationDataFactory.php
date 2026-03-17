<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Hybridauth\User\Profile;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

class RegistrationDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CountryFacade $countryFacade,
    ) {
    }

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

        $registrationData->telephone = PhoneData::fromArray($input['telephone']);
        $registrationData->country = $this->countryFacade->findByCode($input['country']);

        return $registrationData;
    }

    public function createForDomainId(int $domainId): RegistrationData
    {
        $registrationData = $this->create();
        $registrationData->domainId = $domainId;

        return $registrationData;
    }

    public function create(): RegistrationData
    {
        return new RegistrationData();
    }

    public function createFromSocialNetworkProfile(Profile $profile): RegistrationData
    {
        $registrationData = $this->createForDomainId($this->domain->getId());

        $registrationData->firstName = TransformStringHelper::getTrimmedStringOrNullOnEmpty($profile->firstName);
        $registrationData->lastName = TransformStringHelper::getTrimmedStringOrNullOnEmpty($profile->lastName);
        $registrationData->email = TransformStringHelper::getTrimmedStringOrNullOnEmpty($profile->email);

        return $registrationData;
    }
}
