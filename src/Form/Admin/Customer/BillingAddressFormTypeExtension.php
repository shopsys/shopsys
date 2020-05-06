<?php

declare(strict_types=1);

namespace App\Form\Admin\Customer;

use App\Component\Form\FormBuilderHelper;
use App\Component\Locale\LocaleHelper;
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
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(FormBuilderHelper $formBuilderHelper, Domain $domain)
    {
        $this->formBuilderHelper = $formBuilderHelper;
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /* @var $customerUser \App\Model\Customer\User\CustomerUser */
        $customerUser = $options['customerUser'];

        $userLocale = LocaleHelper::LOCALE_CS;
        $domainId = Domain::SECOND_DOMAIN_ID;
        if ($customerUser !== null) {
            $domainId = $customerUser->getDomainId();
            $userLocale = $this->domain->getDomainConfigById($domainId)->getLocale();
        }

        $builderCompanyDataGroup = $builder->get('companyData')->get('companyFields');

        $builderCompanyDataGroup->add('companyTaxNumber', TextType::class, [
            'required' => false,
            'constraints' => [
                new Constraints\Length([
                    'max' => 50,
                    'maxMessage' => 'Tax number cannot be longer than {{ limit }} characters',
                    'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                ]),
            ],
            'label' => t('Tax number', [], null, $userLocale),
        ]);

        if ($domainId === Domain::SECOND_DOMAIN_ID) {
            $builderCompanyDataGroup->add(
                'companyNumberWithVat',
                TextType::class,
                [
                    'label' => t('Tax number with vat'),
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Vyplňte prosím DIČ']),
                        new Constraints\Length(['max' => 50, 'maxMessage' => 'Vyplňte prosím DIČ kratší než {{ limit }} znaků.']),
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
