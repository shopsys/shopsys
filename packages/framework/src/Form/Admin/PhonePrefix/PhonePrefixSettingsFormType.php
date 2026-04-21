<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PhonePrefix;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Country\CountryFlag;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCodeProvider;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PhonePrefixSettingsFormType extends AbstractType
{
    public function __construct(
        private readonly CountryDialCodeProvider $countryDialCodeProvider,
        private readonly CountryFacade $countryFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $countryDialCodes = $this->countryDialCodeProvider->getAll();
        $preferredCountryDialCodes = $this->getPreferredDialCodesByCountriesOnDomain(
            $options['domain_id'],
            $countryDialCodes,
        );

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add('enabledCodes', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'choices' => $countryDialCodes,
                'choice_label' => $this->createPhonePrefixLabel(...),
                'choice_value' => 'code',
                'preferred_choices' => $preferredCountryDialCodes,
                'label' => 'Enabled phone prefixes',
                'constraints' => [
                    new Constraints\NotBlank(message: 'At least one phone prefix is required'),
                ],
            ])
            ->add('defaultCode', ChoiceType::class, [
                'required' => true,
                'choices' => $countryDialCodes,
                'choice_label' => $this->createPhonePrefixLabel(...),
                'choice_value' => 'code',
                'preferred_choices' => $preferredCountryDialCodes,
                'placeholder' => '-- Choose default prefix --',
                'label' => 'Default phone prefix',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose default phone prefix'),
                ],
            ]);

        $builderSettingsGroup->get('enabledCodes')
            ->addModelTransformer(new CountryDialCodeTransformer($countryDialCodes, multiple: true));

        $builderSettingsGroup->get('defaultCode')
            ->addModelTransformer(new CountryDialCodeTransformer($countryDialCodes, multiple: false));

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => PhonePrefixSettingsData::class,
                'constraints' => [
                    new Constraints\Callback($this->validateDefaultPrefixIsEnabled(...)),
                ],
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    public function validateDefaultPrefixIsEnabled(
        PhonePrefixSettingsData $phonePrefixSettingsData,
        ExecutionContextInterface $executionContext,
    ): void {
        if ($phonePrefixSettingsData->defaultCode !== null
            && !in_array($phonePrefixSettingsData->defaultCode, $phonePrefixSettingsData->enabledCodes, true)
        ) {
            $executionContext->buildViolation(t('Default phone prefix must be enabled'))
                ->atPath('defaultCode')
                ->addViolation();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[] $countryDialCodes
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]
     */
    private function getPreferredDialCodesByCountriesOnDomain(int $domainId, array $countryDialCodes): array
    {
        $countries = $this->countryFacade->getAllEnabledOnDomain($domainId);
        $countryCodes = array_map(
            static fn (Country $country): string => $country->getCode(),
            $countries,
        );

        return array_filter(
            $countryDialCodes,
            static function (CountryDialCode $countryDialCode) use ($countryCodes): bool {
                return in_array($countryDialCode->code, $countryCodes, true);
            },
        );
    }

    private function createPhonePrefixLabel(CountryDialCode $countryDialCode): string
    {
        return sprintf(
            '%s %s (%s)',
            CountryFlag::getFlagEmoji($countryDialCode->code),
            $countryDialCode->dialCode,
            Countries::getName($countryDialCode->code),
        );
    }
}
