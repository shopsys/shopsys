<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ProductReviewPolicy;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ProductReviewPolicySettingFormType extends AbstractType
{
    public const string PRODUCT_REVIEW_POLICY_ARTICLE_FIELD_NAME = 'productReviewPolicyArticle';

    public const string MINIMAL_AVERAGE_RATING_FOR_LISTING_FIELD_NAME = 'minimalAverageRatingForListing';

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
            ])
            ->add(self::MINIMAL_AVERAGE_RATING_FOR_LISTING_FIELD_NAME, NumberType::class, [
                'required' => false,
                'scale' => 1,
                'constraints' => [
                    new Constraints\Range(min: 1, max: 5),
                ],
                'invalid_message' => 'Please enter number.',
                'label' => 'Minimal average rating in product lists',
                'help' => t(
                    'Products with a lower average rating do not show stars in product lists (the product detail always shows them). Leave empty to show stars for every reviewed product. It\'s possible to set a value from 1 to 5 with one decimal place (e.g., 4.5).',
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
