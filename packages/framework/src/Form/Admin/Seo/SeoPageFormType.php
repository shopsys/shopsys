<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class SeoPageFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $seoPage = $options['seoPage'];

        $builderMainGroup = $this->createBasicInformationGroup($builder, $seoPage);
        $builderAttributesGroup = $this->createSeoAttributesGroup($builder);
        $builderImageGroup = $this->createImageGroup($builder, $seoPage);

        $builder
            ->add($builderMainGroup)
            ->add($builderAttributesGroup)
            ->add($builderImageGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('seoPage')
            ->addAllowedTypes('seoPage', [SeoPage::class, 'null'])
            ->setDefaults([
                'data_class' => SeoPageData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|null $seoPage
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createBasicInformationGroup(
        FormBuilderInterface $builder,
        ?SeoPage $seoPage,
    ): FormBuilderInterface {
        $group = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => t('Basic information'),
        ]);

        $group
            ->add('pageName', TextType::class, [
                'label' => t('Page name'),
                'required' => true,
                'disabled' => $seoPage !== null,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter page name']),
                ],
            ])
            ->add('pageSlugsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'disabled' => $seoPage !== null,
                'required' => true,
                'label' => t('Page URL'),
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter page URL']),
                    ],
                ],
            ]);

        return $group;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createSeoAttributesGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $group = $builder->create('attributes', GroupType::class, [
            'label' => t('SEO'),
        ]);

        $group
            ->add('seoTitlesIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 60,
                ],
                'label' => t('Page title'),
            ])
            ->add('seoMetaDescriptionsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 155,
                ],
                'label' => t('Meta description'),
            ])
            ->add('canonicalUrlsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => UrlType::class,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Url(['message' => 'Link must be valid URL address']),
                    ],
                ],
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => null,
                ],
                'label' => t('Canonical URL'),
            ])
            ->add('seoOgTitlesIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 60,
                ],
                'label' => t('Open Graph title'),
            ])
            ->add('seoOgDescriptionsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 155,
                ],
                'label' => t('Open Graph description'),
            ]);

        return $group;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|null $seoPage
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createImageGroup(FormBuilderInterface $builder, ?SeoPage $seoPage): FormBuilderInterface
    {
        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => t('Image'),
        ]);

        $builderImageGroup
            ->add('seoOgImage', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => SeoPage::class,
                'image_type' => SeoPageFacade::IMAGE_TYPE_OG,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $seoPage,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => false,
            ]);

        return $builderImageGroup;
    }
}
