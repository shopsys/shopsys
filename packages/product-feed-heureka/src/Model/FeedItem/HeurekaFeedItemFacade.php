<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductRepository;

class HeurekaFeedItemFacade
{
    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductRepository $heurekaProductRepository
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory $feedItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaProductDataBatchLoader $productDataBatchLoader
     */
    public function __construct(
        protected readonly HeurekaProductRepository $heurekaProductRepository,
        protected readonly HeurekaFeedItemFactory $feedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly HeurekaProductDataBatchLoader $productDataBatchLoader,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItem[]|iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->heurekaProductRepository->getProducts(
            $domainConfig,
            $pricingGroup,
            $lastSeekId,
            $maxResults,
        );
        $this->productDataBatchLoader->loadForProducts($products, $domainConfig);

        foreach ($products as $product) {
            yield $this->feedItemFactory->create($product, $domainConfig);
        }
    }
}
