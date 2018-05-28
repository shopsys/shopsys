<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Slider;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class SliderItemFormType extends AbstractType
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter article name']),
                    ],
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
                'icon_title' => t('Name serves only for internal use within the administration'),

            ])
            ->add('link', UrlType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter link']),
                    new Constraints\Url(['message' => 'Link must be valid URL address']),
                ],
                'label' => t('Link'),
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
            'is_group_container_to_render_as_the_last_one' => true,
            'label' => t('Image'),
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => $options['scenario'] === self::SCENARIO_CREATE,
                'constraints' => $imageConstraints,
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
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderImageGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
