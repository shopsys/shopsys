<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Article\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Article\ArticleFormType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
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
        $builderArticleDataGroup->add('createdAt', DatePickerType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter date of creation']),
            ],
            'label' => 'Creation date',
        ]);

        $builderArticleDataGroup->add('external', YesNoType::class, [
            'required' => true,
            'label' => t('Otevírat v novém okně'),
            'position' => ['after' => 'hidden'],
        ]);

        $builderArticleDataGroup->add('type', ChoiceType::class, [
            'required' => true,
            'choices' => [
                t('Stránka') => Article::TYPE_SITE,
                t('Odkaz') => Article::TYPE_LINK,
            ],
            'expanded' => true,
            'multiple' => false,
            'label' => t('Typ'),
            'position' => ['after' => 'external'],
        ]);

        $builderArticleDataGroup->add('url', UrlType::class, [
            'required' => false,
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

        $builderArticleDataGroup->add('text', CKEditorType::class, [
            'required' => false,
            'constraints' => [
                new Constraints\NotBlank([
                    'message' => 'Please enter article content',
                    'groups' => [static::VALIDATION_GROUP_TYPE_SITE],
                ]),
            ],
            'label' => t('Content'),
        ]);

        $builder->add($builderArticleDataGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ArticleFormType::class;
    }
}
