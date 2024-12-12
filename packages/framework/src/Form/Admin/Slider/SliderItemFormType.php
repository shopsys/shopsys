<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Slider;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\NumberSliderType;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class SliderItemFormType extends AbstractType
{
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_EDIT = 'edit';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraints = [];

        if ($options['scenario'] === self::SCENARIO_CREATE) {
            $imageConstraints[] = new Constraints\NotBlank(['message' => 'Please choose image']);
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['slider_item']->getId(),
                    'label' => t('ID'),
                ])
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'attr' => ['readonly' => 'readonly'],
                    'label' => t('Domain'),
                ]);
        }

        if ($options['scenario'] === self::SCENARIO_CREATE) {
            $builderSettingsGroup->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ]);
        }

        $builderSettingsGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'label' => t('Name'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Name serves only for internal use within the administration'),
                ],
            ])
            ->add('link', UrlType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter link']),
                    new Constraints\Url(['message' => 'Link must be valid URL address']),
                ],
                'label' => t('Link'),
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => t('Description'),
            ])
            ->add('rgbBackgroundColor', ColorPickerType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter description box background color']),
                    new Constraints\Regex([
                        'pattern' => '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/',
                        'message' => 'Description background color must be a valid hexadecimal color code e.g. #fff or #ffffff',
                    ]),
                ],
                'label' => t('Description background color'),
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
                'label' => t('Description opacity'),
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\NotNull([
                        'message' => 'Please choose visibility',
                    ]),
                ],
                'label' => t('Hide'),
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => t('Image'),
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
                'label' => t('Upload image'),
                'entity' => $options['slider_item'],
                'info_text' => t('You can upload following formats: PNG, JPG'),
                'extensions' => [ImageProcessor::EXTENSION_JPG, ImageProcessor::EXTENSION_JPEG, ImageProcessor::EXTENSION_PNG],
                'hide_delete_button' => $options['scenario'] === self::SCENARIO_EDIT,
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderImageGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
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
