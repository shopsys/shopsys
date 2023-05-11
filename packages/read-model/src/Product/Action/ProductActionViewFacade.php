<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Action;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductActionViewFacade implements ProductActionViewFacadeInterface
{
    protected ProductCollectionFacade $productCollectionFacade;

    protected Domain $domain;

    protected ProductActionViewFactory $productActionViewFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     */
    public function __construct(ProductCollectionFacade $productCollectionFacade, Domain $domain, ProductActionViewFactory $productActionViewFactory)
    {
        $this->productCollectionFacade = $productCollectionFacade;
        $this->domain = $domain;
        $this->productActionViewFactory = $productActionViewFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView[]
     */
    public function getForProducts(array $products): array
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            $products,
            $this->domain->getCurrentDomainConfig()
        );

        $productActionViews = [];

        foreach ($products as $product) {
            $productId = $product->getId();

            $productActionViews[$productId] = $this->productActionViewFactory->createFromProduct(
                $product,
                $absoluteUrlsIndexedByProductId[$productId]
            );
        }

        return $productActionViews;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    public function getForProduct(Product $product): ProductActionView
    {
        return $this->getForProducts([$product])[$product->getId()];
    }
}
