<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Image\View\ImageViewFactory;

/**
 * @experimental
 */
class ListedProductViewFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\View\ImageViewFactory
     */
    protected $imageViewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\View\ProductActionViewFactory
     */
    protected $productActionViewsFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\View\ImageViewFactory $imageViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\View\ProductActionViewFactory $productActionViewsFactory
     */
    public function __construct(
        ImageViewFactory $imageViewFactory,
        ProductActionViewFactory $productActionViewsFactory
    ) {
        $this->imageViewFactory = $imageViewFactory;
        $this->productActionViewsFactory = $productActionViewsFactory;
    }
    /**
     * @param array $listedProductViewsData
     * @return \Shopsys\FrameworkBundle\Model\Product\View\ListedProductView[]
     */
    public function getListedProductViews(array $listedProductViewsData): array
    {
        $imageViewsIndexedByProductIds = $this->imageViewFactory->getImageViewsOrNullsIndexedByEntityIds(
            Product::class,
            $this->getAllIds($listedProductViewsData)
        );
        $actionViewsIndexedByProductIds = $this->productActionViewsFactory->getProductActionViewsIndexedByProductIds($listedProductViewsData);

        $listedProductsViews = [];
        foreach ($listedProductViewsData as $listedProductViewData) {
            $productId = $listedProductViewData['id'];
            $actionView = $actionViewsIndexedByProductIds[$productId];
            $listedProductsViews[] = new ListedProductView(
                $productId,
                $listedProductViewData['name'],
                $imageViewsIndexedByProductIds[$productId],
                $listedProductViewData['availabilityName'],
                $listedProductViewData['sellingPrice'],
                $listedProductViewData['shortDescription'],
                $listedProductViewData['flagIds'],
                $actionView
            );
        }

        return $listedProductsViews;
    }

    /**
     * @param array $listedProductViewsData
     * @return int[]
     */
    protected function getAllIds(array $listedProductViewsData): array
    {
        $allIds = [];
        foreach ($listedProductViewsData as $listedProductViewData) {
            $allIds[] = $listedProductViewData['id'];
        }

        return $allIds;
    }
}
