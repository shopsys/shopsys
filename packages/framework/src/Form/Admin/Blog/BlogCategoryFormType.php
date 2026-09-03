<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Blog;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BlogCategoryFormType extends AbstractType
{
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Localization $localization,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $this->createSettingsGroup($builder, $options);
        $builderSeoGroup = $this->createSeoGroup($builder, $options);
        $builderDescriptionGroup = $this->createDescriptionGroup($builder);
        $builderImageGroup = $this->createImageGroup($builder, $options);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderSeoGroup)
            ->add($builderDescriptionGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_blogcategory_list',
                'entity' => $options['blogCategory'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['blogCategory'])
            ->setAllowedTypes('blogCategory', [BlogCategory::class, 'null'])
            ->setDefaults([
                'data_class' => BlogCategoryData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    private function createSettingsGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        if ($options['blogCategory'] !== null) {
            $parentChoices = $this->blogCategoryFacade->getTranslatedAllWithoutBranch($options['blogCategory'], $this->localization->getCurrentLocaleForTranslatableEntities());
        } else {
            $parentChoices = $this->blogCategoryFacade->getTranslatedAll($this->localization->getCurrentLocaleForTranslatableEntities());
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        if ($options['blogCategory'] !== null) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['blogCategory']->getId(),
                    'label' => 'ID',
                ]);
        }

        $builderSettingsGroup
            ->add('names', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(max: 255, maxMessage: 'Name cannot be longer than {{ limit }} characters'),
                    ],
                ],
                'label' => 'Name',
            ])
            ->add('parent', ChoiceType::class, [
                'required' => true,
                'choices' => $parentChoices,
                'choice_label' => function (BlogCategory $blogCategory) {
                    $padding = str_repeat("\u{00a0}", $blogCategory->getLevel() * 2);

                    return $padding . $blogCategory->getName();
                },
                'choice_value' => 'id',
                'label' => 'Ancestor category',
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ]);

        return $builderSettingsGroup;
    }

    private function createSeoGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder->create('seoGroup', SeoGroupType::class, [
            'placeholder_source_input_id' => 'blog_category_form_settings_names_{locale}',
            'url_list_options' => $options['blogCategory'] !== null ? [
                'route_name' => 'front_blogcategory_detail',
                'entity_id' => $this->getBlogCategoryId($options['blogCategory']),
            ] : null,
        ]);
    }

    private function getBlogCategoryId(?BlogCategory $blogCategory): ?int
    {
        if ($blogCategory !== null) {
            return $blogCategory->getId();
        }

        return null;
    }

    private function createDescriptionGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('description', GroupType::class, [
            'label' => 'Description',
        ]);

        $builderDescriptionGroup
            ->add('descriptions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'label' => 'Description',
                'required' => false,
            ]);

        return $builderDescriptionGroup;
    }

    private function createImageGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'label' => 'Upload image',
                'entity' => $options['blogCategory'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        return $builderImageGroup;
    }
}
