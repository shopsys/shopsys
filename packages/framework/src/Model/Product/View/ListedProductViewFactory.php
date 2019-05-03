<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Component\Normalizer\Normalizer;
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
     * @var \Shopsys\FrameworkBundle\Component\Normalizer\Normalizer
     */
    protected $normalizer;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\View\ImageViewFactory $imageViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\View\ProductActionViewFactory $productActionViewsFactory
     * @param \Shopsys\FrameworkBundle\Component\Normalizer\Normalizer $normalizer
     */
    public function __construct(
        ImageViewFactory $imageViewFactory,
        ProductActionViewFactory $productActionViewsFactory,
        Normalizer $normalizer
    ) {
        $this->imageViewFactory = $imageViewFactory;
        $this->productActionViewsFactory = $productActionViewsFactory;
        $this->normalizer = $normalizer;
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

        foreach ($listedProductViewsData as $index => $listedProductViewData) {
            $productId = $listedProductViewData['id'];
            $listedProductViewsData[$index]['action'] = $actionViewsIndexedByProductIds[$productId];
            $listedProductViewsData[$index]['image'] = $imageViewsIndexedByProductIds[$productId];
        }

        return $this->normalizer->denormalizeArray($listedProductViewsData, $this->getViewObjectName());
    }

    /**
     * @return string
     */
    protected function getViewObjectName(): string
    {
        return ListedProductView::class;
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
