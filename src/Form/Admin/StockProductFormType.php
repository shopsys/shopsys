<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Stock\ProductStock;
use App\Model\Stock\ProductStockData;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Model\Product\Product;
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
                'constraints' => [
                    new Constraints\GreaterThanOrEqual(['value' => 0]),
                    new Constraints\Regex(['pattern' => '/^\d+$/']),
                ],
            ])
            ->add('productExposed', YesNoType::class, [
                'label' => t('Je vystaven'),
            ])
            ->add('futureProductQuantity', DisplayOnlyType::class, [
                'mapped' => true,
            ])
            ->add('dateOfStorage', DisplayOnlyType::class, [
                'mapped' => true,
            ])
            ->add('daysOfStorage', DisplayOnlyType::class, [
                'mapped' => true,
            ])
        ;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductStockData::class,
            ])
        ;
    }
}
