<?php

declare(strict_types=1);

namespace App\Form\Admin\Customer;

use App\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class BillingAddressFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        private FormBuilderHelper $formBuilderHelper,
        private CountryFacade $countryFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);

        $countries = $this->countryFacade->getAllEnabledOnDomain($options['domain_id']);

        $this->makeBillingAddressRequired($builder, $countries);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield BillingAddressFormType::class;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \Shopsys\FrameworkBundle\Model\Country\Country[] $countries
     */
    private function makeBillingAddressRequired(FormBuilderInterface $builder, array $countries): void
    {
        $builderAddressGroup = $builder->get('address');

        $builderAddressGroup->remove('street');
        $builderAddressGroup->remove('city');
        $builderAddressGroup->remove('postcode');
        $builderAddressGroup->remove('country');

        $builderAddressGroup
            ->add('street', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter street',
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Street name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Street'),
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter city',
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'City name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('City'),
            ])
            ->add('postcode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter zip code',
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Postcode'),
            ])
            ->add('country', ChoiceType::class, [
                'required' => true,
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please choose country',
                    ]),
                ],
                'label' => t('Country'),
            ]);
    }
}
