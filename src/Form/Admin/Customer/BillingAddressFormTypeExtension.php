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
        /* @var $customerUser \App\Model\Customer\User\CustomerUser|null */
        $customerUser = $options['customerUser'];

        $domainId = Domain::SECOND_DOMAIN_ID;
        if ($customerUser !== null) {
            $domainId = $customerUser->getDomainId();
        }

        $builderCompanyDataGroup = $builder->get('companyData')->get('companyFields');

        $builderCompanyDataGroup->add(
            'companyVatNumber',
            TextType::class,
            [
                'label' => $domainId === DOMAIN::FIRST_DOMAIN_ID ? t('Vat number') : t('IČ DPH'),
                'required' => true,
                'constraints' => [
                    new Constraints\Length([
                        'min' => 12,
                        'max' => 12,
                        'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                    new Constraints\Regex([
                        'pattern' => RegexValidationRule::COMPANY_VAT_NUMBER_REGEX,
                        'message' => 'Prosím, zadávejte pouze čísla',
                        'groups' => [BillingAddressFormType::VALIDATION_GROUP_COMPANY_CUSTOMER],
                    ]),
                ],
            ]
        );

        if ($domainId === Domain::FIRST_DOMAIN_ID) {
            $builderCompanyDataGroup->remove('companyTaxNumber');
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
