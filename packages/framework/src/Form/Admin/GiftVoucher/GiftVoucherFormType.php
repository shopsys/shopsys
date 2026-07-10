<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\GiftVoucher;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Constraints\PositiveMoneyAmount;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyOrderType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherData;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GiftVoucherFormType extends AbstractType
{
    public function __construct(
        private readonly GiftVoucherStatusEnum $giftVoucherStatusEnum,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher|null $giftVoucher */
        $giftVoucher = $options['gift_voucher'];

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($giftVoucher instanceof GiftVoucher) {
            $builderSettingsGroup
                ->add('code', DisplayOnlyType::class, [
                    'label' => 'Code',
                    'data' => $giftVoucher->getCode(),
                ]);
        }

        $builderSettingsGroup
            ->add('valueWithVat', MoneyType::class, [
                'scale' => 6,
                'required' => true,
                'currency' => $options['currency_code'],
                'constraints' => [
                    new NotBlank(message: 'Please enter the value'),
                    new PositiveMoneyAmount(),
                ],
                'label' => 'Value',
            ])
            ->add('validUntil', DateTimeType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Please enter the validity date'),
                ],
                'label' => 'Valid until',
            ]);

        if ($giftVoucher instanceof GiftVoucher) {
            $builderSettingsGroup
                ->add('status', ChoiceType::class, [
                    'required' => true,
                    'choices' => $this->giftVoucherStatusEnum->getAllIndexedByTranslations(),
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'label' => 'Status',
                ]);

            if ($giftVoucher->getCreatedOnOrder() !== null) {
                $builderSettingsGroup
                    ->add('purchasedInOrder', DisplayOnlyOrderType::class, [
                        'label' => 'Purchased in order',
                        'order' => $giftVoucher->getCreatedOnOrder(),
                    ]);
            }

            if ($giftVoucher->getRedeemedOnOrder() !== null) {
                $builderSettingsGroup
                    ->add('redeemedInOrder', DisplayOnlyOrderType::class, [
                        'label' => 'Redeemed in order',
                        'order' => $giftVoucher->getRedeemedOnOrder(),
                    ]);
            }
        }

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_giftvoucher_list',
                'entity' => $giftVoucher,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['gift_voucher', 'currency_code'])
            ->setAllowedTypes('gift_voucher', [GiftVoucher::class, 'null'])
            ->setAllowedTypes('currency_code', 'string')
            ->setDefaults([
                'data_class' => GiftVoucherData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
