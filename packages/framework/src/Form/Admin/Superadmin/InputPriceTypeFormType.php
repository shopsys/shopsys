<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Superadmin;

use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class InputPriceTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    t('Excluding VAT') => PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
                    t('Including VAT') => PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
                ],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter input prices']),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
