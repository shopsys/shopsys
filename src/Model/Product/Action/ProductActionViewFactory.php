<?php

declare(strict_types=1);

namespace App\Model\Product\Action;

use App\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView as BaseProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory as BaseProductActionViewFactory;

class ProductActionViewFactory extends BaseProductActionViewFactory
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    protected ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductAvailabilityFacade $productAvailabilityFacade,
        Domain $domain
    ) {
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $absoluteUrl
     * @return \App\Model\Product\Action\ProductActionView
     */
    public function createFromProduct(BaseProduct $product, string $absoluteUrl): BaseProductActionView
    {
        $isProductAvailable = $this->productAvailabilityFacade->isProductAvailableOnDomainOrHasPreorder(
            $product,
            $this->domain->getId()
        );

        return new ProductActionView(
            $product->getId(),
            $product->isSellingDenied(),
            $product->isMainVariant(),
            $absoluteUrl,
            $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId()),
            $isProductAvailable,
            $product->hasPreorder()
        );
    }

    /**
     * @param array $productArray
     * @return \App\Model\Product\Action\ProductActionView
     */
    public function createFromArray(array $productArray): BaseProductActionView
    {
        return new ProductActionView(
            $productArray['id'],
            $productArray['selling_denied'],
            $productArray['is_main_variant'],
            $productArray['detail_url'],
            $productArray['stock_quantity'],
            $productArray['in_stock'],
            $productArray['has_preorder']
        );
    }
}
