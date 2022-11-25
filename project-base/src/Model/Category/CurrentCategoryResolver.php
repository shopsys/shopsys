<?php

declare(strict_types=1);

namespace App\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\HttpFoundation\Request;

class CurrentCategoryResolver
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ProductFacade $productFacade
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->productFacade = $productFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $domainId
     * @return \App\Model\Category\Category|null
     */
    public function findCurrentCategoryByRequest(Request $request, int $domainId): ?Category
    {
        $routeName = $request->get('_route');

        if ($routeName === 'front_product_list') {
            $categoryId = $request->get('id');
            /** @var \App\Model\Category\Category $currentCategory */
            $currentCategory = $this->categoryFacade->getById($categoryId);

            return $currentCategory;
        }

        if ($routeName === 'front_product_detail') {
            $productId = $request->get('id');
            $product = $this->productFacade->getById($productId);
            /** @var \App\Model\Category\Category $currentCategory */
            $currentCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);

            return $currentCategory;
        }

        return null;
    }
}
