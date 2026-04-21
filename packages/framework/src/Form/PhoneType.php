<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use libphonenumber\PhoneNumberUtil;
use Override;
use Shopsys\FrameworkBundle\Component\Country\CountryFlag;
use Shopsys\FrameworkBundle\Form\Admin\PhonePrefix\CountryDialCodeTransformer;
use Shopsys\FrameworkBundle\Form\Constraints\PhoneNumber;
use Shopsys\FrameworkBundle\Form\Constraints\PhoneNumberPrefixConsistency;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCodeProvider;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

final class PhoneType extends AbstractType
{
    private ?string $originalUnknownPrefix = null;

    public function __construct(
        private readonly CountryDialCodeProvider $countryDialCodeProvider,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // form fields are added in PRE_SET_DATA listener to have access to the data for unknown prefix handling
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options): void {
                $this->handlePreSetData($event, $options);
            },
        );
        $builder->addEventListener(FormEvents::POST_SUBMIT, $this->handlePostSubmit(...));
    }

    private function handlePreSetData(FormEvent $event, array $options): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $countryDialCodes = $this->countryDialCodeProvider->getAll();
        $preferredCountryDialCodes = $options['domain_id'] !== null
            ? $this->getEnabledDialCodesOnDomain($options['domain_id'], $countryDialCodes)
            : [];

        $this->originalUnknownPrefix = null;

        if ($data instanceof PhoneData) {
            $this->resolveCountryCodeFromStoredPrefix($data);

            if ($data->countryCode === CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE && $data->prefix !== null) {
                $this->originalUnknownPrefix = $data->prefix;
                $countryDialCodes[] = new CountryDialCode(CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE, $data->prefix);
            }
        }

        $formFactory = $form->getConfig()->getFormFactory();
        $countryCodeBuilder = $formFactory->createNamedBuilder('countryCode', ChoiceType::class, null, [
            'choices' => $countryDialCodes,
            'preferred_choices' => $preferredCountryDialCodes,
            'choice_label' => $this->createPhonePrefixLabel(...),
            'choice_value' => 'code',
            'placeholder' => '-- Choose prefix --',
            'required' => $options['required'],
            'label' => false,
            'constraints' => $options['required'] ? [new Constraints\NotBlank(message: 'Please enter phone prefix', groups: $options['constraint_groups'])] : [],
            'auto_initialize' => false,
        ]);
        $countryCodeBuilder->addModelTransformer(new CountryDialCodeTransformer($countryDialCodes));

        $form->add($countryCodeBuilder->getForm());
        $form->add('number', TelType::class, [
            'required' => $options['required'],
            'label' => false,
            'constraints' => $options['required'] ? [new Constraints\NotBlank(message: 'Please enter telephone number', groups: $options['constraint_groups'])] : [],
        ]);
    }

    private function handlePostSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (!$data instanceof PhoneData) {
            return;
        }

        if ($data->number === null || $data->number === '') {
            $data->prefix = null;
            $data->countryCode = null;

            return;
        }

        if ($data->countryCode === null) {
            $data->prefix = null;

            return;
        }

        if ($data->countryCode === CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE && $this->originalUnknownPrefix !== null) {
            $data->prefix = $this->originalUnknownPrefix;

            return;
        }

        if ($data->countryCode !== CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE) {
            $data->prefix = $this->countryDialCodeProvider->getDialCodeForCountryCode($data->countryCode);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => PhoneData::class,
                'required' => true,
                'constraint_groups' => [Constraint::DEFAULT_GROUP],
                'constraints' => static fn (Options $options): array => [
                    new PhoneNumber(domainId: $options['domain_id'], groups: $options['constraint_groups']),
                    new PhoneNumberPrefixConsistency(groups: $options['constraint_groups']),
                ],
            ])
            ->setAllowedTypes('constraints', ['array', Constraint::class])
            ->setAllowedTypes('constraint_groups', ['array'])
            ->setDefault('domain_id', null)
            ->setAllowedTypes('domain_id', ['int', 'null']);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['unknownPrefix'] = null;
        $view->vars['isSuspicious'] = false;

        $data = $form->getData();

        if (!$data instanceof PhoneData) {
            return;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        $view->vars['isSuspicious'] = !$phoneUtil->isValidNumber($data->toLibPhoneNumberObject());
        $view->vars['unknownPrefix'] = $data->countryCode === CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE ? $data->prefix : null;
    }

    private function resolveCountryCodeFromStoredPrefix(PhoneData $data): void
    {
        $storedCountryCode = $data->countryCode;
        $storedPrefix = $data->prefix;

        if ($storedCountryCode === CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE) {
            return;
        }

        if (!$storedPrefix && !$storedCountryCode) {
            return;
        }

        if (!$storedCountryCode) {
            $resolvedCode = $this->countryDialCodeProvider->getCountryCodeForDialCode($storedPrefix);
            $data->countryCode = $resolvedCode ?: CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE;

            return;
        }

        $expectedPrefix = $this->countryDialCodeProvider->getDialCodeForCountryCode($storedCountryCode);

        if ($expectedPrefix === null || $expectedPrefix !== $storedPrefix) {
            $data->countryCode = CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE;
        }
    }

    private function createPhonePrefixLabel(CountryDialCode $countryDialCode): string
    {
        if ($countryDialCode->code === CountryDialCodeProvider::UNKNOWN_COUNTRY_CODE) {
            return sprintf('⚠️ %s (%s)', t('Unknown prefix'), $countryDialCode->dialCode);
        }

        return sprintf(
            '%s %s (%s)',
            CountryFlag::getFlagEmoji($countryDialCode->code),
            $countryDialCode->dialCode,
            Countries::getName($countryDialCode->code),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[] $allCountryDialCodes
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]
     */
    private function getEnabledDialCodesOnDomain(int $domainId, array $allCountryDialCodes): array
    {
        $enabledCountryDialCodes = $this->countryDialCodeProvider->getAllEnabledOnDomain($domainId);

        $enabledCodesByCountryCode = array_fill_keys(
            array_map(
                static fn (CountryDialCode $countryDialCode): string => $countryDialCode->code,
                $enabledCountryDialCodes,
            ),
            true,
        );

        return array_filter(
            $allCountryDialCodes,
            static fn (CountryDialCode $countryDialCode): bool => isset($enabledCodesByCountryCode[$countryDialCode->code]),
        );
    }
}
