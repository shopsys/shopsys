<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Blog;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BlogArticleAuthorFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor|null $blogArticleAuthor */
        $blogArticleAuthor = $options['blogArticleAuthor'];

        $builderBasicInformationGroup = $builder->create('basicInformation', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($blogArticleAuthor !== null) {
            $builderBasicInformationGroup
                ->add('id', DisplayOnlyType::class, [
                    'label' => 'ID',
                    'data' => $blogArticleAuthor->getId(),
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
            ->add('jobTitles', LocalizedType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Job title cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'label' => 'Job title',
            ])
            ->add('descriptions', LocalizedType::class, [
                'required' => false,
                'entry_type' => TextareaType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => 'Description',
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);
        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => BlogArticleAuthor::class,
                'file_constraints' => [
                    new Constraints\Image(
                        maxSize: '2M',
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
                    ),
                ],
                'entity' => $blogArticleAuthor,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => 'Upload image',
            ]);

        $builder
            ->add($builderBasicInformationGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_crud_blog_article_author_list',
                'entity' => $blogArticleAuthor,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('blogArticleAuthor')
            ->setAllowedTypes('blogArticleAuthor', [BlogArticleAuthor::class, 'null'])
            ->setDefaults([
                'data_class' => BlogArticleAuthorData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
