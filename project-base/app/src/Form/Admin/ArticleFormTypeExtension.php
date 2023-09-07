<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Article\Article;
use App\Model\Article\ArticleData;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Article\ArticleFormType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ArticleFormTypeExtension extends AbstractTypeExtension
{
    public const VALIDATION_GROUP_TYPE_SITE = 'typeSite';
    public const VALIDATION_GROUP_TYPE_LINK = 'typeLink';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderArticleDataGroup = $builder->get('articleData');

        $builderArticleDataGroup->add('external', YesNoType::class, [
            'required' => true,
            'label' => t('Open in new window'),
            'position' => [
                'after' => 'hidden',
            ],
        ]);

        $builderArticleDataGroup->add('type', ChoiceType::class, [
            'required' => true,
            'choices' => [
                t('Site') => Article::TYPE_SITE,
                t('Link') => Article::TYPE_LINK,
            ],
            'expanded' => true,
            'multiple' => false,
            'label' => t('Type'),
            'position' => [
                'after' => 'external',
            ],
        ]);

        $builderArticleDataGroup->add('url', UrlType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank([
                    'message' => 'Please enter URL',
                    'groups' => [static::VALIDATION_GROUP_TYPE_LINK],
                ]),
            ],
            'label' => t('URL'),
            'position' => ['after' => 'type'],
            'trim' => true,
        ]);

        $builderArticleDataGroup->add('text', GrapesJsType::class, [
            'required' => true,
            'allow_products' => true,
            'constraints' => [
                new Constraints\NotBlank([
                    'message' => 'Please enter article content',
                    'groups' => [static::VALIDATION_GROUP_TYPE_SITE],
                ]),
            ],
            'label' => t('Content'),
        ]);

        $this->changeOptionsOfPlacementField($builderArticleDataGroup);

        $builder->add($builderArticleDataGroup);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ArticleFormType::class;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ArticleData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \App\Model\Article\ArticleData $articleData */
                    $articleData = $form->getData();

                    if ($articleData->type === Article::TYPE_SITE) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_SITE;
                    } elseif ($articleData->type === Article::TYPE_LINK) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_LINK;
                    }

                    return $validationGroups;
                },
            ]);
    }

    /**
     * @param mixed $builderArticleDataGroup
     */
    private function changeOptionsOfPlacementField($builderArticleDataGroup)
    {
        $builderArticleDataGroup->add('placement', ChoiceType::class, [
            'required' => true,
            'choices' => [
                t('Articles in footer') . ' 1' => Article::PLACEMENT_FOOTER_1,
                t('Articles in footer') . ' 2' => Article::PLACEMENT_FOOTER_2,
                t('Articles in footer') . ' 3' => Article::PLACEMENT_FOOTER_3,
                t('Articles in footer') . ' 4' => Article::PLACEMENT_FOOTER_4,
                t('without positioning') => Article::PLACEMENT_NONE,
            ],
            'placeholder' => t('-- Choose article position --'),
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please choose article placement']),
            ],
            'label' => t('Location'),
        ]);
    }
}
