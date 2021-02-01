<?php

declare(strict_types=1);

namespace App\Form\Front\Customer;

use App\Component\Validator\RegexValidationRule;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class BillingAddressFormType extends AbstractType
{
    public const VALIDATION_GROUP_COMPANY_CUSTOMER = 'companyCustomer';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
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
        $countries = $this->countryFacade->getAllEnabledOnDomain($options['domain_id']);

        $builder
            ->add('companyName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter company name',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Company name cannot be longer than {{ limit }} characters',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ])
            ->add('companyNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter identification number',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Length([
                        'min' => 8,
                        'max' => 8,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_NUMBER_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ])
            ->add('companyVatNumber', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'min' => 12,
                        'max' => 12,
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_VAT_NUMBER_REGEX,
                        'message' => 'Musí obsahovat pouze pouze čísla a velká písmena',
                        'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ])
            ->add('street', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím ulici a č. popisné',
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Street name cannot be longer than {{ limit }} characters',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::STREET_NUMBER_REGEX,
                        'message' => 'Ulice musí obsahovat číslo',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::STREET_ALPHABET_REGEX,
                        'message' => 'Ulice musí obsahovat písmeno',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím město',
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'City name cannot be longer than {{ limit }} characters',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ])
            ->add('postcode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím PSČ',
                    ]),
                    new Constraints\Length([
                        'max' => 5,
                        'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters',
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::MOEVE_DENY_CHARS,
                        'message' => 'Prosím, nepoužívejte žádné speciální znaky. Například őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº',
                    ]),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'required' => true,
                'choices' => $countries,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Vyplňte prosím Stát',
                    ]),
                ],
            ]);

        if ($options['domain_id'] === Domain::SECOND_DOMAIN_ID) {
            $builder->add(
                'companyTaxNumber',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\Length(['min' => 10, 'max' => 10]),
                        new Constraints\NotBlank([
                            'message' => 'Vyplňte prosím DIČ-2',
                            'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                        ]),
                        new Constraints\Regex([
                            'pattern' => RegexValidationRule::COMPANY_TAX_NUMBER_REGEX,
                            'message' => 'Prosím, zadávejte pouze čísla',
                            'groups' => [self::VALIDATION_GROUP_COMPANY_CUSTOMER],
                        ]),
                    ],
                ]
            );
        }
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

                    /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
                    $billingAddressData = $form->getData();

                    if ($billingAddressData->companyCustomer) {
                        $validationGroups[] = self::VALIDATION_GROUP_COMPANY_CUSTOMER;
                    }

                    return $validationGroups;
                },
            ]);
    }
}
