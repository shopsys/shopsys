<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoriesType extends AbstractType
{
    public function __construct(
        private readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        private readonly CategoryFacade $categoryFacade,
        private readonly Domain $domain,
        private readonly Localization $localization,
    ) {
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $selectedCategories = $form->getData() ?? [];
        $selectedCategoryIds = array_map('intval', $form->getViewData() ?? []);

        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['main_category_path'] = $this->getMainCategoryPath($options);
        $view->vars['tree_categories'] = $this->categoryFacade->getAllCategoriesOfCollapsedTree($selectedCategories);
        $view->vars['selected_category_ids'] = $selectedCategoryIds;
        $view->vars['checkbox_name'] = $view->vars['full_name'] . '[]';
        $view->vars['checkbox_id_prefix'] = $view->vars['id'];
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->categoriesIdsToCategoriesTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['domain_id'])
            ->setDefined(['product'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('product', [Product::class, 'null'])
            ->setDefaults([
                'required' => false,
                'compound' => false,
                'multiple' => true,
                'empty_data' => [],
                'product' => null,
            ]);
    }

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
