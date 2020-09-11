<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ListedProductVariantsViewFacade implements ListedProductVariantsViewFacadeInterface
{
    /**
     * @var  \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory
     */
    protected $listedProductViewFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     */
    public function __construct(
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ListedProductViewFactory $listedProductViewFactory
    ) {
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->listedProductViewFactory = $listedProductViewFactory;
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
