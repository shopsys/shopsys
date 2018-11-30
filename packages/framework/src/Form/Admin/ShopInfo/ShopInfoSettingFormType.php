<?php

namespace Shopsys\FrameworkBundle\Form\Admin\ShopInfo;

use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopInfoSettingFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderPublishedDataGroup = $builder->create('publishedData', GroupType::class, [
            'label' => t('Published data'),
        ]);

        $builderPublishedDataGroup
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'label' => t('Telephone number'),
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => t('E-mail'),
            ])
            ->add('phoneHours', TextType::class, [
                'required' => false,
                'label' => t('Phone availability'),
            ]);

        $builder
            ->add($builderPublishedDataGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
