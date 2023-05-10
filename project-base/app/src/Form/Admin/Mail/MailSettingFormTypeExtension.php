<?php

declare(strict_types=1);

namespace App\Form\Admin\Mail;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\Admin\Mail\MailSettingFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MailSettingFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $footerGroup = $builder->add('footerGroup', GroupType::class, [
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
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield MailSettingFormType::class;
    }
}
