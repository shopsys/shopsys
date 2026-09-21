<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Hybridauth\User\Profile;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\Exception\SocialNetworkLoginException;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CountryFacade $countryFacade,
        protected readonly ValidatorInterface $validator,
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
        $this->validateDataFromSocialNetwork($profile);

        $registrationData = $this->createForDomainId($this->domain->getId());

        $registrationData->firstName = ExtendedClassNameResolver::resolve(TransformStringHelper::class)::getTrimmedStringOrNullOnEmpty($profile->firstName);
        $registrationData->lastName = ExtendedClassNameResolver::resolve(TransformStringHelper::class)::getTrimmedStringOrNullOnEmpty($profile->lastName);
        $registrationData->email = $profile->email;

        return $registrationData;
    }

    /**
     * @throws \Shopsys\FrontendApiBundle\Model\SocialNetwork\Exception\SocialNetworkLoginException
     */
    protected function validateDataFromSocialNetwork(Profile $profile): void
    {
        $violations = $this->validator->validate($profile->email, [
            new NotBlank(message: 'Email is not filled'),
            new Length(
                max: 255,
                maxMessage: 'Email cannot be longer than {{ limit }} characters',
            ),
            new Email(message: 'Email is not valid'),
        ]);

        if (count($violations) > 0) {
            throw new SocialNetworkLoginException('Data from social network are not valid');
        }
    }
}
