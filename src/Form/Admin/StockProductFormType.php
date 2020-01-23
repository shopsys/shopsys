<?php

declare(strict_types=1);


namespace App\Form\Admin;


use App\Model\Stock\StockProductData;
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
        $builder
            ->add('productQuantity', TextType::class, [
                'label' => $builder->getData()->name,
                'constraints' => [
                    new Constraints\GreaterThan(['value' => -1, 'message' => 'File name cannot be longer than {{ limit }} characters']),
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
                'data_class' => StockProductData::class,
            ]);
    }
}