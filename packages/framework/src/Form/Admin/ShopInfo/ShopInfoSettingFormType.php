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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderPublishedDataGroup = $builder->create('publishedData', GroupType::class, [
            'is_group_container_to_render_as_the_last_one' => true,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
