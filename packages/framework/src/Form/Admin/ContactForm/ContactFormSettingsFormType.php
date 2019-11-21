<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ContactForm;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormSettingsFormType extends AbstractType
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
            ->add('mainText', CKEditorType::class, [
                'required' => false,
                'label' => t('Main text'),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactFormSettingsData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
