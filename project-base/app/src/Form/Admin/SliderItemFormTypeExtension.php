<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Slider\SliderItemFacade;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\Admin\Slider\SliderItemFormType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class SliderItemFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildExtendedTextAndLinkForm($builder);
        $this->buildVisibilityIntervalForm($builder);
        $this->buildImagesGroup($builder, $options);
        $this->buildGtmForm($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    private function buildImagesGroup(FormBuilderInterface $builder, array $options): void
    {
        $builderImageGroup = $builder->get('image');

        $imageConstraints = [];

        if ($options['scenario'] === SliderItemFormType::SCENARIO_CREATE) {
            $imageConstraints[] = new Constraints\NotBlank(['message' => 'Please choose image']);
        }

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => $options['scenario'] === SliderItemFormType::SCENARIO_CREATE,
                'constraints' => $imageConstraints,
                'image_entity_class' => SliderItem::class,
                'image_type' => SliderItemFacade::IMAGE_TYPE_WEB,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                        'mimeTypesMessage' => 'Image can be only in JPG or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'label' => t('Upload image'),
                'entity' => $options['slider_item'],
                'info_text' => t('You can upload following formats: PNG, JPG'),
                'extensions' => [ImageProcessor::EXTENSION_JPG, ImageProcessor::EXTENSION_JPEG, ImageProcessor::EXTENSION_PNG],
                'hide_delete_button' => $options['scenario'] === SliderItemFormType::SCENARIO_EDIT,
            ]);

        $builderImageGroup
            ->add('mobileImage', ImageUploadType::class, [
                'required' => $options['scenario'] === SliderItemFormType::SCENARIO_CREATE,
                'constraints' => $imageConstraints,
                'image_entity_class' => SliderItem::class,
                'image_type' => SliderItemFacade::IMAGE_TYPE_MOBILE,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                        'mimeTypesMessage' => 'Image can be only in JPG or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'label' => t('Upload image for mobile devices'),
                'entity' => $options['slider_item'],
                'info_text' => t('You can upload following formats: PNG, JPG'),
                'extensions' => [ImageProcessor::EXTENSION_JPG, ImageProcessor::EXTENSION_JPEG, ImageProcessor::EXTENSION_PNG],
                'hide_delete_button' => $options['scenario'] === SliderItemFormType::SCENARIO_EDIT,
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildExtendedTextAndLinkForm(FormBuilderInterface $builder): void
    {
        $builder->add('sliderExtendedText', TextType::class, [
            'required' => false,
            'label' => t('Text displayed under banner'),
        ])
        ->add('sliderExtendedTextLink', UrlType::class, [
            'required' => false,
            'label' => t('Link of text under banner'),
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildVisibilityIntervalForm(FormBuilderInterface $builder): void
    {
        $builder->add('datetimeVisibleFrom', DatePickerType::class, [
            'required' => false,
            'label' => t('Display date FROM'),
        ])->add('datetimeVisibleTo', DatePickerType::class, [
            'required' => false,
            'label' => t('Display date TO'),
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildGtmForm(FormBuilderInterface $builder): void
    {
        $builder->add('gtmId', TextType::class, [
            'required' => true,
            'label' => t('GTM ID'),
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter GTM ID']),
            ],
            'attr' => ['placeholder' => t('e.g. Sale-04-20-2020')],
        ])->add('gtmCreative', TextType::class, [
            'required' => false,
            'label' => t('GTM creative'),
            'attr' => ['placeholder' => t('e.g. red-1035x340-jpg-carousel')],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield SliderItemFormType::class;
    }
}
