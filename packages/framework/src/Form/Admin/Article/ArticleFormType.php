<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Article;

use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GrapesJsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ArticleFormType extends AbstractType
{
    private const VALIDATION_GROUP_TYPE_SITE = 'typeSite';
    private const VALIDATION_GROUP_TYPE_LINK = 'typeLink';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     */
    public function __construct(
        private readonly SeoSettingFacade $seoSettingFacade,
        private readonly Domain $domain,
        private readonly ArticleFacade $articleFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $seoMetaDescriptionAttributes = $this->getSeoMetaDescriptionAttributes($options);

        $builderArticleData = $builder->create('articleData', GroupType::class, [
            'label' => t('Article data'),
        ]);

        if ($options['article'] === null) {
            $builderArticleData
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => t('Domain'),
                ])
                ->add('placement', ChoiceType::class, [
                    'required' => true,
                    'choices' => $this->articleFacade->getAvailablePlacementChoices(),
                    'placeholder' => t('-- Choose article position --'),
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please choose article placement']),
                    ],
                    'label' => t('Location'),
                ]);
        } else {
            $builderArticleData
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['article']->getId(),
                    'label' => t('ID'),
                ])
                ->add('domain', DisplayOnlyType::class, [
                    'data' => $this->domain->getDomainConfigById($options['article']->getDomainId())->getName(),
                    'label' => t('Domain'),
                ]);
        }
        $builderArticleData
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter article name']),
                ],
                'label' => t('Name'),
            ])
            ->add('hidden', YesNoType::class, [
                'required' => false,
                'label' => t('Hide'),
            ])
            ->add('external', YesNoType::class, [
                'required' => true,
                'label' => t('Open in new window'),
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Site') => Article::TYPE_SITE,
                    t('Link') => Article::TYPE_LINK,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => t('Type'),
            ])
            ->add('url', UrlType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter URL',
                        'groups' => [self::VALIDATION_GROUP_TYPE_LINK],
                    ]),
                ],
                'label' => t('URL'),
                'trim' => true,
            ])
            ->add('text', GrapesJsType::class, [
                'required' => true,
                'allow_products' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter article content',
                        'groups' => [self::VALIDATION_GROUP_TYPE_SITE],
                    ]),
                ],
                'label' => t('Content'),
            ])
            ->add('createdAt', DatePickerType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter date of creation']),
                ],
                'label' => t('Creation date'),
            ]);

        $builderSeoData = $builder->create('seo', GroupType::class, [
            'label' => t('SEO'),
        ]);

        $builderSeoData
            ->add('seoTitle', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'js-dynamic-placeholder',
                    'data-placeholder-source-input-id' => 'article_form_name',
                ],
                'label' => t('Page title'),
                'macro' => [
                    'name' => 'seoFormRowMacros',
                    'recommended_length' => 60,
                ],
            ])
            ->add('seoMetaDescription', TextareaType::class, [
                'required' => false,
                'attr' => $seoMetaDescriptionAttributes,
                'label' => t('Meta description'),
                'macro' => [
                    'name' => 'seoFormRowMacros',
                    'recommended_length' => 155,
                ],
            ])
            ->add('seoH1', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'js-dynamic-placeholder',
                    'data-placeholder-source-input-id' => 'article_form_name',
                ],
                'label' => t('Heading (H1)'),
            ]);

        if ($options['article'] !== null) {
            $builderSeoData
                ->add('urls', UrlListType::class, [
                    'label' => t('URL addresses'),
                    'route_name' => 'front_article_detail',
                    'entity_id' => $options['article']->getId(),
                ]);
        }

        $builder
            ->add($builderArticleData)
            ->add($builderSeoData)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
     * @param array $options
     * @return string[]
     */
    private function getSeoMetaDescriptionAttributes(array $options)
    {
        $seoMetaDescriptionAttributes = [];

        $descriptionsMainPageByDomainIds = $this->seoSettingFacade
            ->getDescriptionsMainPageIndexedByDomainIds($this->domain->getAll());
        $seoMetaDescriptionAttributes['placeholder'] = $this->seoSettingFacade->getDescriptionMainPage(
            $options['domain_id'],
        );

        foreach ($descriptionsMainPageByDomainIds as $domainId => $description) {
            $seoMetaDescriptionAttributes['data-placeholder-domain' . $domainId] = $description;
        }

        return $seoMetaDescriptionAttributes;
    }
}
