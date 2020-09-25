<?php

declare(strict_types=1);

namespace App\Form\Admin\Customer;

use App\Component\Form\FormBuilderHelper;
use App\Component\Validator\RegexValidationRule;
use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class BillingAddressFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [
        'companyCustomer',
        'companyFields',
        'address',
    ];

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(FormBuilderHelper $formBuilderHelper)
    {
        $this->formBuilderHelper = $formBuilderHelper;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /* @var $customerUser \App\Model\Customer\User\CustomerUser */
        $customerUser = $options['customerUser'];

        $domainId = Domain::SECOND_DOMAIN_ID;
        if ($customerUser !== null) {
            $domainId = $customerUser->getDomainId();
        }

        $builderCompanyDataGroup = $builder->get('companyData')->get('companyFields');

        $builderCompanyDataGroup->add('companyTaxNumber', TextType::class, [
            'required' => false,
            'constraints' => [
                new Constraints\Length([
                    'min' => 12,
                    'max' => 12,
                    'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                ]),
                new Constraints\Regex([
                    'pattern' => RegexValidationRule::COMPANY_TAX_NUMBER_REGEX,
                    'message' => 'Musí obsahovat pouze pouze čísla a velká písmena',
                    'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                ]),
            ],
            'label' => 'Tax number',
        ]);

        if ($domainId === Domain::SECOND_DOMAIN_ID) {
            $builderCompanyDataGroup->add(
                'companyNumberWithVat',
                TextType::class,
                [
                    'label' => t('Tax number with vat'),
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank([
                            'message' => 'Vyplňte prosím DIČ-2',
                            'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                        ]),
                        new Constraints\Length([
                            'min' => 10,
                            'max' => 10,
                            'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                        ]),
                        new Constraints\Regex([
                            'pattern' => RegexValidationRule::COMPANY_NUMBER_WITH_VAT_REGEX,
                            'message' => 'Prosím, zadávejte pouze čísla',
                            'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                        ]),
                    ],
                ]
            );
        }

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['customerUser'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null']);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield BillingAddressFormType::class;
    }
}
