<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Mail;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class MailSettingFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        $builderSettingsGroup
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Email(['message' => 'Please enter valid email']),
                ],
                'label' => t('Main administrator email'),
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter full name']),
                ],
                'label' => t('Email name'),
            ]);

        $footerGroup = $builder->create('footerGroup', GroupType::class, [
            'label' => t('Footer'),
        ]);

        $footerGroup->add('facebookUrl', TextType::class, [
            'label' => t('Facebook URL'),
        ]);
        $footerGroup->add('instagramUrl', TextType::class, [
            'label' => t('Instagram URL'),
        ]);
        $footerGroup->add('youtubeUrl', TextType::class, [
            'label' => t('Youtube URL'),
        ]);
        $footerGroup->add('linkedinUrl', TextType::class, [
            'label' => t('LinkedIn URL'),
        ]);
        $footerGroup->add('tiktokUrl', TextType::class, [
            'label' => t('TikTok URL'),
        ]);
        $footerGroup->add('footerText', CKEditorType::class, [
            'label' => t('Footer Text'),
        ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($footerGroup)
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
