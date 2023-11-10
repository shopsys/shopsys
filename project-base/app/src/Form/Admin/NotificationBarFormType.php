<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\NotificationBar\NotificationBar;
use App\Model\NotificationBar\NotificationBarData;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class NotificationBarFormType extends AbstractType
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_EDIT = 'edit';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'required' => false,
            'label' => t('Settings'),
        ]);

        $domainIdAttributes = [];

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $domainIdAttributes = ['readonly' => true];
        }

        $builderSettingsGroup
            ->add('domainId', DomainType::class, [
                'label' => t('Domain'),
                'attr' => $domainIdAttributes,
            ])
            ->add('text', CKEditorType::class, [
                'required' => false,
                'label' => t('Content'),
                'constraints' => [
                    new NotBlank(['message' => 'Please enter notification bar content']),
                ],
            ])
            ->add('rgbColor', ColorPickerType::class, [
                'label' => t('Background color'),
                'constraints' => [
                    new NotBlank(['message' => 'Please enter flag color']),
                    new Length([
                        'max' => 7,
                        'maxMessage' => 'Flag color must be in valid hexadecimal code e.g. #3333ff',
                    ]),
                ],
            ])
            ->add('validityFrom', DatePickerType::class, [
                'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
                'required' => false,
                'label' => t('Valid from'),
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('validityTo', DatePickerType::class, [
                'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
                'required' => false,
                'label' => t('Valid to'),
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'label' => t('Hide'),
            ])
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => NotificationBar::class,
                'image_type' => null,
                'file_constraints' => [
                    new Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                          . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $options['notification_bar'],
                'label' => t('Upload new image'),
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
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
                    new Callback([$this, 'checkDateValidity']),
                ],
            ]);
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBarData $notificationBarData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
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
