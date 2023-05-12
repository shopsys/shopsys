<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ListedProductVariantsViewFacade implements ListedProductVariantsViewFacadeInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     */
    public function __construct(
        protected readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        protected readonly ListedProductViewFactory $listedProductViewFactory
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAllVariants(int $productId): array
    {
        $product = $this->productOnCurrentDomainFacade->getVisibleProductById($productId);

        if (!$product->isMainVariant()) {
            return [];
        }

        return $this->listedProductViewFactory->createFromProducts(
            $this->productOnCurrentDomainFacade->getVariantsForProduct($product)
        );
    }
}
