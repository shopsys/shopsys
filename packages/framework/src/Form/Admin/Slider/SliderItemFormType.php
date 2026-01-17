<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Slider;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\NumberSliderType;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SliderItemFormType extends AbstractType
{
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_EDIT = 'edit';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraints = [];

        if ($options['scenario'] === self::SCENARIO_CREATE) {
            $imageConstraints[] = new Constraints\NotBlank(['message' => 'Please choose image']);
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['slider_item']->getId(),
                    'label' => 'ID',
                ])
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'attr' => ['readonly' => 'readonly'],
                    'label' => 'Domain',
                ]);
        }

        if ($options['scenario'] === self::SCENARIO_CREATE) {
            $builderSettingsGroup->add('domainId', DomainType::class, [
                'required' => true,
                'label' => 'Domain',
            ]);
        }

        $builderSettingsGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'label' => 'Name',
                'help' => t('Name serves only for internal use within the administration'),
            ])
            ->add('link', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter link']),
                ],
                'label' => 'Link',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('rgbBackgroundColor', ColorPickerType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter description box background color']),
                ],
                'label' => 'Description background color',
            ])
            ->add('opacity', NumberSliderType::class, [
                'required' => true,
                'scale' => 2,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter description box opacity']),
                    new Constraints\Range([
                        'min' => 0,
                        'max' => 1,
                        'notInRangeMessage' => 'Opacity must be between {{ min }} and {{ max }}',
                    ]),
                ],
                'label' => 'Description opacity',
            ])
            ->add('hidden', YesNoType::class, [
                'label' => 'Hide',
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => $options['scenario'] === self::SCENARIO_CREATE,
                'constraints' => $imageConstraints,
                'image_entity_class' => SliderItem::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                        'mimeTypesMessage' => 'Image can be only in JPG or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'label' => 'Upload image',
                'entity' => $options['slider_item'],
                'info_text' => t('You can upload following formats: PNG, JPG'),
                'extensions' => [ImageProcessor::EXTENSION_JPG, ImageProcessor::EXTENSION_JPEG, ImageProcessor::EXTENSION_PNG],
                'hide_delete_button' => $options['scenario'] === self::SCENARIO_EDIT,
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderImageGroup)
            ->add('datetimeVisibleFrom', DatePickerType::class, [
                'required' => false,
                'label' => 'Display date FROM',
            ])
            ->add('datetimeVisibleTo', DatePickerType::class, [
                'required' => false,
                'label' => 'Display date TO',
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_slider_list',
                'entity' => $options['slider_item'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['scenario', 'slider_item'])
            ->addAllowedTypes('slider_item', [SliderItem::class, 'null'])
            ->addAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->setDefaults([
                'data_class' => SliderItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
