<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Article;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GrapesJsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ArticleFormType extends AbstractType
{
    private const string VALIDATION_GROUP_TYPE_SITE = 'typeSite';
    private const string VALIDATION_GROUP_TYPE_LINK = 'typeLink';

    public function __construct(
        private readonly Domain $domain,
        private readonly ArticleFacade $articleFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Article\Article|null $article */
        $article = $options['article'];

        $builderArticleData = $builder->create('articleData', GroupType::class, [
            'label' => 'Article data',
        ]);

        if ($article === null) {
            $builderArticleData
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => 'Domain',
                ])
                ->add('placement', ChoiceType::class, [
                    'required' => true,
                    'choices' => $this->articleFacade->getAvailablePlacementChoices(),
                    'placeholder' => '-- Choose article position --',
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please choose article placement'),
                    ],
                    'label' => 'Location',
                ]);
        } else {
            $builderArticleData
                ->add('id', DisplayOnlyType::class, [
                    'data' => $article->getId(),
                    'label' => 'ID',
                ])
                ->add('domain', DisplayOnlyType::class, [
                    'data' => $this->domain->getDomainConfigById($article->getDomainId())->getName(),
                    'label' => 'Domain',
                ]);
        }
        $builderArticleData
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter article name'),
                ],
                'label' => 'Name',
            ])
            ->add('hidden', YesNoType::class, [
                'label' => 'Hide',
            ])
            ->add('external', YesNoType::class, [
                'label' => 'Open in new window',
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Site') => Article::TYPE_SITE,
                    t('Link') => Article::TYPE_LINK,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Type',
            ])
            ->add('url', UrlType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter URL',
                        groups: [self::VALIDATION_GROUP_TYPE_LINK],
                    ),
                ],
                'label' => 'URL',
                'trim' => true,
                'row_attr' => [
                    'data-js-article-type-content' => 'link',
                ],
            ])
            ->add('text', GrapesJsType::class, [
                'required' => true,
                'allow_products' => true,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter article content',
                        groups: [self::VALIDATION_GROUP_TYPE_SITE],
                    ),
                ],
                'label' => 'Content',
                'row_attr' => [
                    'data-js-article-type-content' => 'site',
                ],
            ])
            ->add('createdAt', DatePickerType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter date of creation'),
                ],
                'label' => 'Creation date',
                'row_attr' => [
                    'data-js-article-type-content' => 'site',
                ],
            ]);

        $builderSeoData = $builder->create('seoGroup', SeoGroupType::class, [
            'multidomain' => false,
            'placeholder_source_input_id' => 'article_form_articleData_name',
            'url_list_options' => $article !== null ? [
                'route_name' => 'front_article_detail',
                'entity_id' => $article->getId(),
                'limit_domains_by_ids' => [$article->getDomainId()],
            ] : null,
            'row_attr' => [
                'data-js-article-type-content' => 'site',
            ],
        ]);

        $builder
            ->add($builderArticleData)
            ->add($builderSeoData)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_article_list',
                'entity' => $article,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['article', 'domain_id'])
            ->setAllowedTypes('article', [Article::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => ArticleData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData */
                    $articleData = $form->getData();

                    if ($articleData->type === Article::TYPE_SITE) {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_SITE;
                    } elseif ($articleData->type === Article::TYPE_LINK) {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_LINK;
                    }

                    return $validationGroups;
                },
            ]);
    }

    /**
     * @return array<string, int|string|null>
     */
}
