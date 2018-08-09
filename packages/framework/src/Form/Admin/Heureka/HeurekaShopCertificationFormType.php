<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Heureka;

use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class HeurekaShopCertificationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
            'is_group_container_to_render_as_the_last_one' => true,
        ]);

        $builderSettingsGroup
            ->add('heurekaApiKey', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'min' => 32,
                        'max' => 32,
                        'exactMessage' => 'Heureka API must be {{ limit }} characters',
                    ]),
                ],
                'label' => t('Code of service Heureka - Verified by Customer'),
                'icon_title' => t('Enter 32-digit code which will be sent to server') . ' ' . $options['server_name'],
            ])
            ->add('heurekaWidgetCode', TextareaType::class, [
                'required' => false,
                'label' => t('Heureka Widget code'),
                'attr' => [
                    'class' => 'height-150',
                ],
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('server_name')
            ->setAllowedTypes('server_name', ['string', 'null'])
            ->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
