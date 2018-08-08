<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductMassActionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selectType', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Only checked products') => ProductMassActionData::SELECT_TYPE_CHECKED,
                    t('All search results') => ProductMassActionData::SELECT_TYPE_ALL_RESULTS,
                ],
            ])
            ->add('action', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Set') => ProductMassActionData::ACTION_SET,
                ],
            ])
            ->add('subject', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Hiding product') => ProductMassActionData::SUBJECT_PRODUCT_HIDDEN,
                ],
            ])
            ->add('value', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Hide') => ProductMassActionData::VALUE_PRODUCT_HIDE,
                    t('Display') => ProductMassActionData::VALUE_PRODUCT_SHOW,
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'data_class' => ProductMassActionData::class,
        ]);
    }
}
