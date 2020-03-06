<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Model\Product\ProductBreadcrumbGenerator as BaseProductBreadcrumbGenerator;

/**
 * @method \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[] getCategoryBreadcrumbItems(\App\Model\Category\Category $category)
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method __construct(\App\Model\Product\ProductRepository $productRepository, \App\Model\Category\CategoryFacade $categoryFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @property \App\Model\Product\ProductRepository $productRepository
 */
class ProductBreadcrumbGenerator extends BaseProductBreadcrumbGenerator
{
    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->productRepository->getById($routeParameters['id']);

        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId(
            $product,
            $this->domain->getId()
        );

        $breadcrumbItems = $this->getCategoryBreadcrumbItems($productMainCategory);

        $breadcrumbItems[] = new BreadcrumbItem(
            $product->getFullname()
        );

        return $breadcrumbItems;
    }
}
