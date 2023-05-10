<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\ProductStockData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class StockProductFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('productQuantity', TextType::class, [
            'empty_data' => 0,
            'attr' => [
                'placeholder' => '0',
            ],
            'constraints' => [
                new Constraints\GreaterThanOrEqual(['value' => 0]),
                new Constraints\Regex(['pattern' => '/^\d+$/']),
            ],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductStockData::class,
            ]);
    }
}
