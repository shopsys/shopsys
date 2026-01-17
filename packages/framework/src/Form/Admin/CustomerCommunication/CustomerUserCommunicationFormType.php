<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class CustomerUserCommunicationFormType extends AbstractType
{
    public const string ORDER_SENT_CONTENT_FIELD_NAME = 'order-sent-content';
    public const string PAYMENT_FAILED_CONTENT_FIELD_NAME = 'payment-failed-content';
    public const string PAYMENT_SUCCESSFUL_CONTENT_FIELD_NAME = 'payment-successful-content';
    public const string PAYMENT_IN_PROCESS_CONTENT_FIELD_NAME = 'payment-in-progress-content';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add(self::ORDER_SENT_CONTENT_FIELD_NAME, CKEditorType::class, [
                'label' => 'Order sent page content',
                'required' => false,
            ])
            ->add(self::PAYMENT_SUCCESSFUL_CONTENT_FIELD_NAME, CKEditorType::class, [
                'label' => 'Payment successful page content',
                'required' => false,
            ])
            ->add(self::PAYMENT_IN_PROCESS_CONTENT_FIELD_NAME, CKEditorType::class, [
                'label' => 'Payment in process page content',
                'required' => false,
            ])
            ->add(self::PAYMENT_FAILED_CONTENT_FIELD_NAME, CKEditorType::class, [
                'label' => 'Payment failed page content',
                'required' => false,
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }
}
