<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\DateTimeHelper\DateTimeHelper;
use App\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class AdvertFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(AdminDomainTabsFacade $adminDomainTabsFacade)
    {
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
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

        $builderImageGroup
            ->add('mobileImage', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Advert::class,
                'image_type' => AdvertFacade::IMAGE_TYPE_MOBILE,
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
                'label' => t('Nahrát nový obrázek pro mobilní zařízení'),
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
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Datum zobrazení OD'),
        ])->add('datetimeVisibleTo', DatePickerType::class, [
            'view_timezone' => DateTimeHelper::UTC_TIMEZONE,
            'required' => false,
            'label' => t('Datum zobrazení DO'),
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
