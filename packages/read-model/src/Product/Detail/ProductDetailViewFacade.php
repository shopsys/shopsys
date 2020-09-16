<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ProductDetailViewFacade implements ProductDetailViewFacadeInterface
{
    /**
     * @var \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewFactory
     */
    protected $productDetailViewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @param \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewFactory $productDetailViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     */
    public function __construct(
        ProductDetailViewFactory $productDetailViewFactory,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
    ) {
        $this->productDetailViewFactory = $productDetailViewFactory;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function getVisibleProductDetail(int $productId): ProductDetailView
    {
        $product = $this->productOnCurrentDomainFacade->getVisibleProductById($productId);

        return $this->productDetailViewFactory->createFromProduct($product);
    }
}
