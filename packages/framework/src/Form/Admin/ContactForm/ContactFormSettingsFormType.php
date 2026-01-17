<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ContactForm;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactFormSettingsFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Main text',
        ]);

        $builderSettingsGroup
            ->add('mainText', CKEditorType::class, [
                'required' => false,
                'label' => false,
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactFormSettingsData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
