<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesTypeTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoriesType extends AbstractType
{
    public function __construct(
        private readonly CategoriesTypeTransformer $categoriesTypeTransformer,
        private readonly CategoryFacade $categoryFacade,
        private readonly Domain $domain,
        private readonly Localization $localization,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['main_category_path'] = $this->getMainCategoryPath($options);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer($this->categoriesTypeTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $entryOptionsNormalizer = function (Options $options, $value) {
            $value['domain_id'] = $value['domain_id'] ?? $options['domain_id'];

            return $value;
        };

        $resolver
            ->setRequired(['domain_id'])
            ->setDefined(['product'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('product', [Product::class, 'null'])
            ->setDefaults([
                'required' => false,
                'entry_type' => CategoryCheckboxType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'product' => null,
            ]);

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getMainCategoryPath(array $options): ?string
    {
        if ($options['product'] === null) {
            return null;
        }

        try {
            $domainConfig = $this->domain->getDomainConfigById($options['domain_id']);
            $categoriesInPath = $this->categoryFacade->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(
                $options['product'],
                $domainConfig,
                $this->localization->getCurrentLocaleForTranslatableEntities(),
            );

            return implode(' > ', $categoriesInPath);
        } catch (CategoryNotFoundException) {
            return null;
        }
    }
}
