<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoriesType extends AbstractType
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
        protected readonly Localization $localization,
    ) {
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['main_category_path'] = $this->getMainCategoryPath($options);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['product'])
            ->setAllowedTypes('product', [Product::class, 'null'])
            ->setDefaults([
                'branch_load_route' => 'admin_category_loadbranchjson',
                'product' => null,
                'data_provider' => $this->categoryFacade,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return TreeSelectionType::class;
    }

    private function getMainCategoryPath(array $options): ?string
    {
        if ($options['product'] === null || $options['domain_id'] === null) {
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
