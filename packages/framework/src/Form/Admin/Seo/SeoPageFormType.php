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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SeoPageFormType extends AbstractType
{
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $seoPage = $options['seoPage'];

        $builderMainGroup = $this->createBasicInformationGroup($builder, $seoPage);
        $builderOpenGraphGroup = $this->createOpenGraphGroup($builder, $seoPage);

        $builder
            ->add($builderMainGroup)
            ->add('seoGroup', SeoGroupType::class)
            ->add($builderOpenGraphGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_seopage_list',
                'entity' => $options['seoPage'],
            ]);
    }

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

    private function createOpenGraphGroup(FormBuilderInterface $builder, ?SeoPage $seoPage): FormBuilderInterface
    {
        $builderOpenGraphGroup = $builder->create('openGraphGroup', GroupType::class, [
            'label' => 'Open Graph',
        ]);

        $builderOpenGraphGroup
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
            ])
            ->add('seoOgImage', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => SeoPage::class,
                'image_type' => SeoPageFacade::IMAGE_TYPE_OG,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $seoPage,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => false,
            ]);

        return $builderOpenGraphGroup;
    }
}
