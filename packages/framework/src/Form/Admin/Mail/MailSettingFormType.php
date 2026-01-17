<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Mail;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class MailSettingFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Email(['message' => 'Please enter valid email']),
                ],
                'label' => 'Main administrator email',
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter full name']),
                ],
                'label' => 'Email name',
            ]);

        $footerGroup = $builder->create('footerGroup', GroupType::class, [
            'label' => 'Footer',
        ]);

        $footerGroup->add('facebookUrl', TextType::class, [
            'label' => 'Facebook URL',
            'required' => false,
        ]);
        $footerGroup->add('instagramUrl', TextType::class, [
            'label' => 'Instagram URL',
            'required' => false,
        ]);
        $footerGroup->add('youtubeUrl', TextType::class, [
            'label' => 'Youtube URL',
            'required' => false,
        ]);
        $footerGroup->add('linkedinUrl', TextType::class, [
            'label' => 'LinkedIn URL',
            'required' => false,
        ]);
        $footerGroup->add('tiktokUrl', TextType::class, [
            'label' => 'TikTok URL',
            'required' => false,
        ]);
        $footerGroup->add('footerText', CKEditorType::class, [
            'label' => 'Footer Text',
        ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($footerGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
