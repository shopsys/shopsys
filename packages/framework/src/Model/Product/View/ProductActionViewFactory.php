<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;

/**
 * @experimental
 */
class ProductActionViewFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade
     */
    protected $productCollectionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductCollectionFacade $productCollectionFacade,
        Domain $domain
    ) {
        $this->productCollectionFacade = $productCollectionFacade;
        $this->domain = $domain;
    }

    /**
     * @param array $listedProductViewsData
     * @return \Shopsys\FrameworkBundle\Model\Product\View\ProductActionView[]
     */
    public function getProductActionViewsIndexedByProductIds(array $listedProductViewsData): array
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            $this->getAllIds($listedProductViewsData),
            $this->domain->getCurrentDomainConfig()
        );
        $productActionViews = [];
        foreach ($listedProductViewsData as $listedProductViewData) {
            $productId = $listedProductViewData['id'];
            $productActionViews[$productId] = new ProductActionView(
                $productId,
                $listedProductViewData['sellingDenied'],
                $listedProductViewData['mainVariant'],
                $absoluteUrlsIndexedByProductId[$productId]
            );
        }

        return $productActionViews;
    }

    /**
     * @param array $listedProductViewsData
     * @return int[]
     */
    protected function getAllIds(array $listedProductViewsData) {
        $allIds = [];
        foreach ($listedProductViewsData as $listedProductViewData) {
            $allIds[] = $listedProductViewData['id'];
        }

        return $allIds;
    }
}
