<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Country;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Form\Constraints\NotInArray;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class CountryFormType extends AbstractType
{
    protected ?Country $country = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(protected readonly CountryFacade $countryFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->country = $options['country'];

        if ($this->country instanceof Country) {
            $builder->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $this->country->getId(),
            ]);
        }

        $builder
            ->add('names', LocalizedType::class, [
                'required' => true,
                'entry_options' => [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter country name'),
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Country name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'label' => 'Name',
            ])
            ->add('code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter country code'),
                    new Constraints\Length(
                        max: 2,
                        maxMessage: 'Country code cannot be longer than {{ limit }} characters',
                    ),
                    new NotInArray(
                        array: $this->getOtherCountryCodes(),
                        message: 'Country code with this code already exists',
                    ),
                ],
                'label' => 'Code',
                'help_html' => true,
                'help' => t('Country code in <a href="%wikiLink%" target="_blank">ISO 3166-1 alpha-2</a>', [
                    '%wikiLink%' => 'https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2',
                ]),
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ])
            ->add('priority', MultidomainType::class, [
                'entry_type' => IntegerType::class,
                'entry_options' => [
                    'help' => t(
                        'The higher the priority, the higher the country will be shown in the listings. Countries with the same priority will be sorted alphabetically.',
                    ),
                    'required' => false,
                    'constraints' => [
                        new Constraints\Type(type: 'numeric'),
                        new Constraints\GreaterThanOrEqual(value: 0),
                    ],
                ],
                'required' => false,
                'label' => 'Priority',
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_country_list',
                'entity' => $options['country'],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('country')
            ->setAllowedTypes('country', [Country::class, 'null'])
            ->setDefaults([
                'data_class' => CountryData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @return string[]
     */
    protected function getOtherCountryCodes(): array
    {
        $otherCountryCodes = [];

        foreach ($this->countryFacade->getAll() as $country) {
            if ($country !== $this->country) {
                $otherCountryCodes[] = $country->getCode();
            }
        }

        return $otherCountryCodes;
    }
}
