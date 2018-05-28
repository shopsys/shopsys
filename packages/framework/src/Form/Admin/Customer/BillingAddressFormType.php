<?php

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
    const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    private $countryFacade;

    public function __construct(CountryFacade $countryFacade)
    {
        $this->countryFacade = $countryFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countries = $this->countryFacade->getAllByDomainId($options['domain_id']);

        $builderCompanyDataGroup = $builder->create('companyData', GroupType::class, [
            'label' => t('Company data'),
            'attr' => [
                'id' => 'customer_form_billingAddressData',
            ],
        ]);

        $builderCompanyDataGroup
            ->add('companyCustomer', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'data-checkbox-toggle-container-class' => 'js-company-fields',
                    'class' => 'js-checkbox-toggle',
                ],
                'label' => t('I buy on company behalf'),
            ])
            ->add(
                $builderCompanyDataGroup
                    ->create('companyFields', FormType::class, [
                        'inherit_data' => true,
                        'attr' => ['class' => 'js-company-fields form-line__js'],
                        'render_form_row' => false,
                    ])
                    ->add('companyName', TextType::class, [
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank([
                                'message' => 'Please enter company name',
                                'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                            new Constraints\Length([
                                'max' => 100,
                                'maxMessage' => 'Company name cannot be longer than {{ limit }} characters',
                                'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                        ],
                        'label' => t('Company'),
                    ])
                    ->add('companyNumber', TextType::class, [
                        'required' => true,
                        'constraints' => [
                            new Constraints\NotBlank([
                                'message' => 'Please enter identification number',
                                'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                            new Constraints\Length([
                                'max' => 50,
                                'maxMessage' => 'Identification number cannot be longer then {{ limit }} characters',
                                'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
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
                                'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                            ]),
                        ],
                        'label' => t('Tax number'),
                    ])
            );

        $builderAddressGroup = $builder->create('address', GroupType::class, [
            'label' => t('Address'),
        ]);

        $builderAddressGroup
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Telephone number cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Telephone'),
            ])
            ->add('street', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Street name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Street'),
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'City name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('City'),
            ])
            ->add('postcode', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Postcode'),
            ])
            ->add('country', ChoiceType::class, [
                'required' => false,
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
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
            ->setDefaults([
                'data_class' => BillingAddressData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    $billingAddressData = $form->getData();
                    /* @var $billingAddressData \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData */

                    if ($billingAddressData->companyCustomer) {
                        $validationGroups[] = self::VALIDATION_GROUP_COMPANY_CUSTOMER;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
