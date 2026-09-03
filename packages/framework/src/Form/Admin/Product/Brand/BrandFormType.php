<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Brand;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BrandFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null $brand */
        $brand = $options['brand'];

        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($brand !== null) {
            $builderBasicInformationGroup
                ->add('id', DisplayOnlyType::class, [
                    'label' => 'ID',
                    'data' => $brand->getId(),
                ]);
        }

        $builderBasicInformationGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter name'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'Name',
            ])
            ->add('descriptions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Description',
            ]);

        $builderSeoGroup = $builder->create('seoGroup', SeoGroupType::class, [
            'placeholder_source_input_id' => 'brand_form_basicInformation_name',
            'url_list_options' => $brand !== null ? [
                'route_name' => 'front_brand_detail',
                'entity_id' => $brand->getId(),
            ] : null,
        ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);
        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Brand::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $brand,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => 'Upload image',
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderSeoGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_brand_list',
                'entity' => $options['brand'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('brand')
            ->setAllowedTypes('brand', [Brand::class, 'null'])
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
