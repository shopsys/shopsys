<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer;

use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class BillingAddressFormType extends AbstractType
{
    public const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(private readonly CountryFacade $countryFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries = $this->countryFacade->getAllEnabledOnDomain($options['domain_id']);

        $builderCompanyDataGroup = $builder->create('companyData', GroupType::class, [
            'label' => t('Company data'),
        ]);

        $builderCompanyDataGroup
            ->add('companyCustomer', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'data-checkbox-toggle-container-class' => 'js-company-fields',
                    'class' => 'js-checkbox-toggle',
                ],
                'disabled' => $options['disableCompanyCustomerCheckbox'],
                'label' => t('I buy on company behalf'),
            ])
            ->add(
                $builderCompanyDataGroup
                    ->create('companyFields', FormType::class, [
                        'inherit_data' => true,
                        'attr' => [
                            'class' => 'js-company-fields form-line__js',
                        ],
                        'render_form_row' => false,
                    ])
                    ->add('companyName', TextType::class, [
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank([
                                'message' => 'Please enter company name',
                                'groups' => [static::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                            new Constraints\Length([
                                'max' => 100,
                                'maxMessage' => 'Company name cannot be longer than {{ limit }} characters',
                                'groups' => [static::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                        ],
                        'label' => t('Company'),
                    ])
                    ->add('companyNumber', TextType::class, [
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank([
                                'message' => 'Please enter identification number',
                                'groups' => [static::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                            new Constraints\Length([
                                'max' => 50,
                                'maxMessage' => 'Identification number cannot be longer than {{ limit }} characters',
                                'groups' => [static::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                        ],
                        'label' => t('Company number'),
                    ])
                    ->add('companyTaxNumber', TextType::class, [
                        'required' => false,
                        'constraints' => [
                            new Constraints\Length([
                                'max' => 50,
                                'maxMessage' => 'Tax number cannot be longer than {{ limit }} characters',
                                'groups' => [static::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                        ],
                        'label' => t('Tax number'),
                    ]),
            );

        $builderAddressGroup = $builder->create('address', GroupType::class, [
            'label' => t('Address'),
        ]);

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

        $builder
            ->add($builderCompanyDataGroup)
            ->add($builderAddressGroup);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('domain_id')
            ->addAllowedTypes('domain_id', 'int')
            ->setDefined('disableCompanyCustomerCheckbox')
            ->setAllowedTypes('disableCompanyCustomerCheckbox', 'bool')
            ->setDefaults([
                'data_class' => BillingAddressData::class,
                'disableCompanyCustomerCheckbox' => false,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData */
                    $billingAddressData = $form->getData();

                    if ($billingAddressData->companyCustomer) {
                        $validationGroups[] = static::VALIDATION_GROUP_COMPANY_CUSTOMER;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
