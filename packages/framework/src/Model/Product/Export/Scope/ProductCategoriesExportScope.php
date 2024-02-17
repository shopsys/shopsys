<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductCategoriesExportScope extends AbstractProductExportScope
{

    public function __construct(
        private readonly CategoryFacade $categoryFacade,
    )
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'categories' => $this->extractCategories($domainId, $object),
            'main_category_id' => $this->categoryFacade->getProductMainCategoryByDomainId(
                $object,
                $domainId,
            )->getId(),
        ];
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractCategories(int $domainId, Product $product): array
    {
        $categoryIds = [];
        $categoriesIndexedByDomainId = $product->getCategoriesIndexedByDomainId();

        if (isset($categoriesIndexedByDomainId[$domainId])) {
            foreach ($categoriesIndexedByDomainId[$domainId] as $category) {
                $categoryIds[] = $category->getId();
            }
        }

        return $categoryIds;
    }
}