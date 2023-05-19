<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductRepository;

class ZboziFeedItemFacade
{
    /**
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductRepository $zboziProductRepository
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory $feedItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader
     * @param \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade $zboziProductDomainFacade
     */
    public function __construct(
        protected readonly ZboziProductRepository $zboziProductRepository,
        protected readonly ZboziFeedItemFactory $feedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly ZboziProductDomainFacade $zboziProductDomainFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->zboziProductRepository->getProducts($domainConfig, $pricingGroup, $lastSeekId, $maxResults);
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);
        $this->productParametersBatchLoader->loadForProducts($products, $domainConfig);

        $zboziProductDomains = $this->zboziProductDomainFacade->getZboziProductDomainsByProductsAndDomainIndexedByProductId(
            $products,
            $domainConfig,
        );

        foreach ($products as $product) {
            $zboziProductDomain = $zboziProductDomains[$product->getId()] ?? null;

            yield $this->feedItemFactory->create($product, $zboziProductDomain, $domainConfig);
        }
    }
}
