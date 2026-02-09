<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\NotificationBar;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class NotificationBarFormType extends AbstractType
{
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_EDIT = 'edit';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'required' => false,
            'label' => 'Settings',
        ]);

        $domainIdAttributes = [];

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $domainIdAttributes = ['readonly' => true];
        }

        $builderSettingsGroup
            ->add('domainId', DomainType::class, [
                'label' => 'Domain',
                'attr' => $domainIdAttributes,
            ])
            ->add('text', CKEditorType::class, [
                'required' => false,
                'label' => 'Content',
                'constraints' => [
                    new NotBlank(message: 'Please enter notification bar content'),
                ],
            ])
            ->add('rgbColor', ColorPickerType::class, [
                'label' => 'Background color',
                'constraints' => [
                    new NotBlank(message: 'Please enter notification bar background color'),
                ],
            ])
            ->add('validityFrom', DateTimeType::class, [
                'required' => false,
                'label' => 'Valid from',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('validityTo', DateTimeType::class, [
                'required' => false,
                'label' => 'Valid to',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('hidden', YesNoType::class, [
                'label' => 'Hide',
            ])
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => NotificationBar::class,
                'image_type' => null,
                'file_constraints' => [
                    new Image(
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $options['notification_bar'],
                'label' => 'Upload new image',
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_notificationbar_list',
                'entity' => $options['notification_bar'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['scenario', 'notification_bar'])
            ->addAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->addAllowedTypes('notification_bar', [NotificationBar::class, 'null'])
            ->setDefaults([
                'data_class' => NotificationBarData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Callback(callback: [$this, 'checkDateValidity']),
                ],
            ]);
    }

    public function checkDateValidity(
        NotificationBarData $notificationBarData,
        ExecutionContextInterface $context,
    ): void {
        if ($notificationBarData->validityFrom !== null
            && $notificationBarData->validityTo !== null
            && $notificationBarData->validityTo <= $notificationBarData->validityFrom
        ) {
            $context->buildViolation(t('"Valid to" must be greater than "Valid from"'))
                ->atPath('validityTo')
                ->addViolation();
        }
    }
}
