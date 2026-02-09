<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Holidays;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportData;
use Spatie\Holidays\Countries\Country as SpatieCountry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class HolidaysImportFormType extends AbstractType
{
    public function __construct(
        private readonly CountryFacade $countryFacade,
        private readonly RouterInterface $router,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'choices' => $this->countryFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'choice_filter' => fn (?Country $country) => $country !== null && SpatieCountry::find($country->getCode()) !== null,
                'placeholder' => 'Select country',
                'required' => true,
                'help_html' => true,
                'help' => t('If the country is not listed, you have to <a href="%countryCreateUrl%" target="_blank">create</a> it first. Or, in rare scenarios, your country code might not be <a href="%supportedCountriesUrl%" target="_blank">supported</a>.', [
                    '%countryCreateUrl%' => $this->router->generate('admin_country_new'),
                    '%supportedCountriesUrl%' => 'https://github.com/spatie/holidays#supported-countries',
                ]),
                'constraints' => [
                    new NotBlank(message: 'Please select a country'),
                ],
            ])
            ->add('year', IntegerType::class, [
                'label' => 'Year',
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Please enter a year'),
                ],
            ])
            ->add('import', ActionBarType::class, [
                'save_label' => 'Import holidays',
            ]);

        if ($this->domain->isMultidomain()) {
            $builder->add('selectedDomains', DomainsType::class, [
                'label' => 'Import for domain',
                'required' => true,
                'constraints' => [
                    new Callback(callback: [$this, 'validateSelectedDomains']),
                ],
            ]);
        }
    }

    /**
     * @param bool[] $selectedDomains
     */
    public function validateSelectedDomains(array $selectedDomains, ExecutionContextInterface $context): void
    {
        $selected = array_filter($selectedDomains);

        if (count($selected) === 0) {
            $context
                ->buildViolation('Please select at least one domain.')
                ->addViolation();
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'data_class' => HolidaysImportData::class,
            ]);
    }
}
