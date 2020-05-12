<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Advert;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class AdvertFormType extends AbstractType
{
    public const VALIDATION_GROUP_TYPE_IMAGE = 'typeImage';
    public const VALIDATION_GROUP_TYPE_CODE = 'typeCode';
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_EDIT = 'edit';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry
     */
    private $advertPositionRegistry;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
     */
    public function __construct(
        Domain $domain,
        AdvertPositionRegistry $advertPositionRegistry
    ) {
        $this->domain = $domain;
        $this->advertPositionRegistry = $advertPositionRegistry;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $imageConstraints = [
            new Constraints\NotBlank([
                'message' => 'Choose image',
                'groups' => [static::VALIDATION_GROUP_TYPE_IMAGE],
            ]),
        ];

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['advert']->getId(),
                    'label' => t('ID'),
                ])
                ->add('domain', DisplayOnlyType::class, [
                    'data' => $this->domain->getDomainConfigById($options['advert']->getDomainId())->getName(),
                    'label' => t('Domain'),
                ]);
        } else {
            $builderSettingsGroup
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                    'label' => t('Domain'),
                ]);
        }

        $builderSettingsGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name of advertisement area']),
                ],
                'label' => t('Name'),
                'icon_title' => t('Name serves only for internal use within the administration'),
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('HTML code') => Advert::TYPE_CODE,
                    t('Image with link') => Advert::TYPE_IMAGE,
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose advertisement type']),
                ],
                'label' => t('Type'),
            ])
            ->add('positionName', ChoiceType::class, [
                'required' => true,
                'choices' => array_flip($this->advertPositionRegistry->getAllLabelsIndexedByNames()),
                'placeholder' => t('-- Choose area --'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose advertisement area']),
                ],
                'label' => t('Area'),
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'label' => t('Hide advertisement'),
            ])
            ->add('code', TextareaType::class, [
                'label' => t('Code'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter HTML code for advertisement area',
                        'groups' => [static::VALIDATION_GROUP_TYPE_CODE],
                    ]),
                ],
                'attr' => [
                    'class' => 'height-150',
                ],
                'js_container' => [
                    'container_class' => 'js-advert-type-content form-line__js',
                    'data_type' => 'code',
                ],
            ]);

        $builderImageGroup = $builder->create('image_group', GroupType::class, [
            'label' => t('Images'),
            'js_container' => [
                'container_class' => 'js-advert-type-content wrap-divider--top',
                'data_type' => 'image',
            ],
        ]);

        $builderImageGroup
            ->add('link', TextType::class, [
                'required' => false,
                'label' => t('Link'),
            ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Advert::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'constraints' => ($options['image_exists'] ? [] : $imageConstraints),
                'label' => t('Upload new image'),
                'entity' => $options['advert'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
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
            ->setRequired(['scenario', 'advert', 'image_exists'])
            ->setAllowedTypes('image_exists', 'bool')
            ->setAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->setAllowedTypes('advert', [Advert::class, 'null'])
            ->setDefaults([
                'image_exists' => false,
                'data_class' => AdvertData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    $advertData = $form->getData();
                    /* @var $advertData \Shopsys\FrameworkBundle\Model\Advert\AdvertData */

                    if ($advertData->type === Advert::TYPE_CODE) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_CODE;
                    } elseif ($advertData->type === Advert::TYPE_IMAGE) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_IMAGE;
                    }
                    return $validationGroups;
                },
            ]);
    }
}
