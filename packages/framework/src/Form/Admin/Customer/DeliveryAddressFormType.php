<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer;

use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class DeliveryAddressFormType extends AbstractType
{
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

        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('First name'),
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter last name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Last name'),
            ])
            ->add('companyName', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Company name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Company'),
            ])
            ->add('street', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter street']),
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
                    new Constraints\NotBlank(['message' => 'Please enter city']),
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
                    new Constraints\NotBlank(['message' => 'Please enter postcode']),
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
                'label' => t('Country'),
            ])
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
            ->add('save', SubmitType::class);
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
                'data_class' => DeliveryAddressData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
