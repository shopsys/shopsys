<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Model\Product\ProductBreadcrumbGenerator as BaseProductBreadcrumbGenerator;

/**
 * @method \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[] getCategoryBreadcrumbItems(\App\Model\Category\Category $category)
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method __construct(\App\Model\Product\ProductRepository $productRepository, \App\Model\Category\CategoryFacade $categoryFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 */
class ProductBreadcrumbGenerator extends BaseProductBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        return $this->getBreadcrumbItemsOnDomain($this->domain->getId(), $routeName, $routeParameters, $this->domain->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItemsOnDomain(
        int $domainId,
        string $routeName,
        array $routeParameters = [],
        ?string $locale = null,
    ): array {
        $product = $this->productRepository->getById($routeParameters['id']);

        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId(
            $product,
            $domainId,
        );

        $breadcrumbItems = $this->getCategoryBreadcrumbItemsOnDomain($productMainCategory, $domainId, $locale);

        $breadcrumbItems[] = new BreadcrumbItem(
            $product->getFullname($locale),
        );

        return $breadcrumbItems;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    protected function getCategoryBreadcrumbItemsOnDomain(Category $category, int $domainId, string $locale): array
    {
        $categoriesInPath = $this->categoryFacade->getVisibleCategoriesInPathFromRootOnDomain(
            $category,
            $domainId,
        );

        $breadcrumbItems = [];

        foreach ($categoriesInPath as $categoryInPath) {
            $breadcrumbItems[] = new BreadcrumbItem(
                $categoryInPath->getName($locale),
                'front_product_list',
                ['id' => $categoryInPath->getId()],
            );
        }

        return $breadcrumbItems;
    }
}
