<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Article\ArticleFormType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class ArticleFormTypeExtension extends AbstractTypeExtension
{
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
            'label' => t('Creation date'),
        ]);
        $builder->add($builderArticleDataGroup);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ArticleFormType::class;
    }
}
