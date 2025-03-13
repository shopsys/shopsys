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

class InputPriceTypeFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => t('Entry price type'),
                'choices' => [
                    t('Excluding VAT') => PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
                    t('Including VAT') => PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
                ],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter input prices']),
                ],
            ])
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
