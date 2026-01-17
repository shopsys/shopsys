<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Brand;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BrandFormType extends AbstractType
{
    public function __construct(
        private readonly Domain $domain,
        private readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null $brand */
        $brand = $options['brand'];

        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1sOptionsByDomainId = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();

            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getTitlePlaceholder($brand),
                    'data-js-placeholder-source-input-id' => 'brand_form_basicInformation_name',
                    'data-js-recommended-length' => 60,
                ],
            ];
            $seoMetaDescriptionsOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->seoSettingFacade->getDescriptionMainPage($domainId),
                    'data-js-recommended-length' => 155,
                ],
            ];
            $seoH1sOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getTitlePlaceholder($brand),
                    'data-js-placeholder-source-input-id' => 'brand_form_basicInformation_name',
                ],
            ];
        }

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
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                    ),
                ],
                'label' => 'Name',
            ])
            ->add('descriptions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Description',
            ]);

        $builderSeoGroup = $builder->create('seo', GroupType::class, [
            'label' => 'SEO',
        ]);
        $builderSeoGroup
            ->add('seoTitles', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoTitlesOptionsByDomainId,
                'label' => 'Page title',
            ])
            ->add('seoMetaDescriptions', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'options_by_domain_id' => $seoMetaDescriptionsOptionsByDomainId,
                'label' => 'Meta description',
            ])
            ->add('seoH1s', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoH1sOptionsByDomainId,
                'label' => 'Heading (H1)',
            ]);

        if ($brand) {
            $builderSeoGroup->add('urls', UrlListType::class, [
                'route_name' => 'front_brand_detail',
                'entity_id' => $brand->getId(),
                'label' => 'URL addresses',
            ]);
        }

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);
        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Brand::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
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

    private function getTitlePlaceholder(?Brand $brand = null): string
    {
        return $brand?->getName() ?? '';
    }
}
