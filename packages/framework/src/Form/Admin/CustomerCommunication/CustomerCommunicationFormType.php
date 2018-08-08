<?php

namespace Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerCommunicationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
            'is_group_container_to_render_as_the_last_one' => true,
        ]);

        $builderSettingsGroup
            ->add('content', CKEditorType::class, ['required' => false]);

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }
}
