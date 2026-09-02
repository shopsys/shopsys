<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ProductReviewPolicy;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductReviewPolicySettingFormType extends AbstractType
{
    public const string PRODUCT_REVIEW_POLICY_ARTICLE_FIELD_NAME = 'productReviewPolicyArticle';

    public function __construct(
        private readonly ArticleFacade $articleFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $articles = $this->articleFacade->getAllByDomainId($options['domain_id']);

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add(self::PRODUCT_REVIEW_POLICY_ARTICLE_FIELD_NAME, ChoiceType::class, [
                'required' => false,
                'choices' => $articles,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => '-- Choose article --',
                'label' => 'Product review policy article',
                'help' => t(
                    'Choose the article that explains how product reviews are collected, verified, and moderated on this domain. Customers see a link to it next to the reviews.',
                ),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
