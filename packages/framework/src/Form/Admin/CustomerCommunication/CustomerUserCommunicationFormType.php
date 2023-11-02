<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerUserCommunicationFormType extends AbstractType
{
    public const ORDER_SENT_CONTENT_FIELD_NAME = 'order-sent-content';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        $builderSettingsGroup
            ->add(self::ORDER_SENT_CONTENT_FIELD_NAME, CKEditorType::class, [
                'label' => t('Order sent page content'),
                'required' => false,
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }
}
