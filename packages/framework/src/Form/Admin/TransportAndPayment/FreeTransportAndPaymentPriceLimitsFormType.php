<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\TransportAndPayment;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class FreeTransportAndPaymentPriceLimitsFormType extends AbstractType
{
    public const string DOMAINS_SUBFORM_NAME = 'priceLimits';
    public const string FIELD_ENABLED = 'enabled';
    public const string FIELD_PRICE_LIMIT = 'priceLimit';
    public const string VALIDATION_GROUP_PRICE_LIMIT_ENABLED = 'priceLimitEnabled';

    public function __construct(private readonly Domain $domain)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add($this->getPriceLimitsBuilder($builder))
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    private function getPriceLimitsBuilder(FormBuilderInterface $builder): FormBuilderInterface
    {
        $formBuilderForDomains = $builder->create(self::DOMAINS_SUBFORM_NAME, null, ['compound' => true]);

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $formBuilderForDomain = $builder->create((string)$domainConfig->getId(), null, [
                'compound' => true,
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];
                    $formData = $form->getData();

                    if ($formData[self::FIELD_ENABLED]) {
                        $validationGroups[] = static::VALIDATION_GROUP_PRICE_LIMIT_ENABLED;
                    }

                    return $validationGroups;
                },
            ])
                ->add(self::FIELD_ENABLED, CheckboxType::class, [
                    'required' => false,
                ])
                ->add(self::FIELD_PRICE_LIMIT, MoneyType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotNegativeMoneyAmount([
                            'message' => 'Price must be greater or equal to zero',
                            'groups' => [static::VALIDATION_GROUP_PRICE_LIMIT_ENABLED],
                        ]),
                        new Constraints\NotBlank([
                            'message' => 'Please enter price',
                            'groups' => [static::VALIDATION_GROUP_PRICE_LIMIT_ENABLED],
                        ]),
                    ],
                    'scale' => 6,
                ]);

            $formBuilderForDomains->add($formBuilderForDomain);
        }

        return $formBuilderForDomains;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
