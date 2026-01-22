<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueSeoPageSlug;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SeoPageFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
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
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_seopage_list',
                'entity' => $options['seoPage'],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
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
            'label' => 'Basic information',
        ]);

        $optionsByDomainId = [];

        foreach ($this->domain->getAll() as $domain) {
            $optionsByDomainId[$domain->getId()] = [
                'constraints' => [
                    new UniqueSeoPageSlug(
                        ignoredSeoPage: $seoPage,
                        domainId: $domain->getId(),
                    ),
                ],
            ];
        }

        $group
            ->add('pageName', TextType::class, [
                'label' => 'Page name',
                'required' => true,
                'disabled' => $seoPage !== null,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter page name'),
                ],
            ])
            ->add('pageSlugsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'disabled' => $seoPage !== null,
                'required' => true,
                'label' => 'Page slug',
                'options_by_domain_id' => $optionsByDomainId,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter page URL'),
                        new Constraints\Regex(
                            pattern: '/^[\w_\-\/]+$/',
                            message: 'Slug can contain only letters, numbers, hyphens, underscores and slashes',
                        ),
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
            'label' => 'SEO',
        ]);

        $group
            ->add('seoTitlesIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'entry_options' => [
                    'attr' => ['data-js-recommended-length' => 60],
                ],
                'label' => 'Page title',
            ])
            ->add('seoMetaDescriptionsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'entry_options' => [
                    'attr' => ['data-js-recommended-length' => 155],
                ],
                'label' => 'Meta description',
            ])
            ->add('canonicalUrlsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => UrlType::class,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Url(message: 'Link must be valid URL address'),
                    ],
                ],
                'required' => false,
                'label' => 'Canonical URL',
            ])
            ->add('seoOgTitlesIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'entry_options' => [
                    'attr' => ['data-js-recommended-length' => 60],
                ],
                'label' => 'Open Graph title',
            ])
            ->add('seoOgDescriptionsIndexedByDomainId', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'entry_options' => [
                    'attr' => ['data-js-recommended-length' => 155],
                ],
                'label' => 'Open Graph description',
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
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('seoOgImage', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => SeoPage::class,
                'image_type' => SeoPageFacade::IMAGE_TYPE_OG,
                'file_constraints' => [
                    new Constraints\Image(
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $seoPage,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => false,
            ]);

        return $builderImageGroup;
    }
}
