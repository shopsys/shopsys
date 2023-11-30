<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Shopsys\FrameworkBundle\Model\Stock\StockSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class StockSettingsFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delivery', TextType::class, [
                'label' => t('Days until stocking'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Regex(['pattern' => '/^\d+$/']),
                    new Constraints\GreaterThanOrEqual(['value' => 0]),
                ],
            ])
            ->add('transfer', TextType::class, [
                'label' => t('Days for transfer between warehouses'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Regex(['pattern' => '/^\d+$/']),
                    new Constraints\GreaterThanOrEqual(['value' => 0]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => StockSettingsData::class,
            ]);
    }
}
