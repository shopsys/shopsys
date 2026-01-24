<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Stock;

use Override;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ProductStockFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('productQuantity', TextType::class, [
            'empty_data' => 0,
            'attr' => [
                'placeholder' => '0',
            ],
            'constraints' => [
                new Constraints\Regex([
                    'pattern' => '/^-?\d+$/',
                    'message' => 'Quantity must be an integer',
                ]),
            ],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductStockData::class,
            ]);
    }
}
