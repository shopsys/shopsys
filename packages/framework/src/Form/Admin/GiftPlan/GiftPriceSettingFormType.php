<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\GiftPlan;

use Override;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GiftPriceSettingFormType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(GiftPlanSettingFacade::GIFT_INPUT_PRICE, MoneyType::class, [
            'scale' => 6,
            'currency' => $options['currency_code'],
            'constraints' => [
                new NotBlank(message: 'Please enter price'),
                new NotNegativeMoneyAmount(message: 'Price must be greater or equal to zero'),
            ],
            'label' => 'Gift price (with VAT)',
        ]);
        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Save gift price setting',
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ])
            ->setRequired(['currency_code'])
            ->setAllowedTypes('currency_code', 'string');
    }
}
