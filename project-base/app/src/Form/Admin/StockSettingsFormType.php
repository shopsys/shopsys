<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\StockSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class StockSettingsFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delivery', TextType::class, [
                'label' => t('Dní do naskladnění'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Regex(['pattern' => '/^\d+$/']),
                    new Constraints\GreaterThanOrEqual(['value' => 0]),
                ],
            ])
            ->add('transfer', TextType::class, [
                'label' => t('Dny pro přesun mezi sklady'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Regex(['pattern' => '/^\d+$/']),
                    new Constraints\GreaterThanOrEqual(['value' => 0]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => StockSettingsData::class,
            ]);
    }
}
