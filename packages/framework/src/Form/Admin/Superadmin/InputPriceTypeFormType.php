<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Superadmin;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class InputPriceTypeFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inputPriceType', ChoiceType::class, [
                'label' => 'Entry price type',
                'choices' => [
                    t('Excluding VAT') => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                    t('Including VAT') => PricingSetting::PRICE_TYPE_WITH_VAT,
                ],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter entry price type']),
                ],
            ])
            ->add('sellingPriceType', ChoiceType::class, [
                'label' => 'Selling price type',
                'choices' => [
                    t('Excluding VAT') => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                    t('Including VAT') => PricingSetting::PRICE_TYPE_WITH_VAT,
                ],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter selling price type']),
                ],
            ])
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
