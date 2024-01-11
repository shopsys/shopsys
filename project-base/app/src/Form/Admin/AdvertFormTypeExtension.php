<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class AdvertFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(private AdminDomainTabsFacade $adminDomainTabsFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildVisibilityIntervalForm($builder);
        $this->buildImageGroup($builder, $options);
        $this->buildSettingsGroup($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    private function buildImageGroup(FormBuilderInterface $builder, array $options): void
    {
        $builderImageGroup = $builder->get('image_group');

        $imageConstraints = [
            new Constraints\NotBlank([
                'message' => 'Choose image',
                'groups' => [AdvertFormType::VALIDATION_GROUP_TYPE_IMAGE],
            ]),
        ];

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Advert::class,
                'image_type' => AdvertFacade::IMAGE_TYPE_WEB,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'constraints' => ($options['web_image_exists'] ? [] : $imageConstraints),
                'label' => t('Upload new image'),
                'entity' => $options['advert'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builderImageGroup
            ->add('mobileImage', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Advert::class,
                'image_type' => AdvertFacade::IMAGE_TYPE_MOBILE,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'constraints' => ($options['mobile_image_exists'] ? [] : $imageConstraints),
                'label' => t('Upload image for mobile devices'),
                'entity' => $options['advert'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildSettingsGroup(FormBuilderInterface $builder): void
    {
        $builderSettingsGroup = $builder->get('settings');
        $builderSettingsGroup->remove('domainId');

        $builderSettingsGroup->add('domain', DisplayOnlyType::class, [
            'data' => $this->adminDomainTabsFacade->getSelectedDomainConfig()->getName(),
            'label' => t('Domain'),
            'position' => 'first',
        ]);

        $builderSettingsGroup->add('categories', CategoriesType::class, [
            'required' => false,
            'label' => t('Assign to category'),
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
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
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['scenario', 'advert', 'web_image_exists', 'mobile_image_exists'])
            ->setAllowedTypes('web_image_exists', 'bool')
            ->setAllowedTypes('mobile_image_exists', 'bool')
            ->setAllowedValues('scenario', [AdvertFormType::SCENARIO_CREATE, AdvertFormType::SCENARIO_EDIT])
            ->setAllowedTypes('advert', [Advert::class, 'null'])
            ->setDefaults([
                'web_image_exists' => false,
                'mobile_image_exists' => false,
                'data_class' => AdvertData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \App\Model\Advert\AdvertData $advertData */
                    $advertData = $form->getData();

                    if ($advertData->type === Advert::TYPE_CODE) {
                        $validationGroups[] = AdvertFormType::VALIDATION_GROUP_TYPE_CODE;
                    } elseif ($advertData->type === Advert::TYPE_IMAGE) {
                        $validationGroups[] = AdvertFormType::VALIDATION_GROUP_TYPE_IMAGE;
                    }

                    return $validationGroups;
                },
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield AdvertFormType::class;
    }
}
