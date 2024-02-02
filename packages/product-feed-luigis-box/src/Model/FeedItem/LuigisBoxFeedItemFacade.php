<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\Product\LuigisBoxProductRepository;

class LuigisBoxFeedItemFacade
{
    /**
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\Model\Product\LuigisBoxProductRepository $luigisBoxProductRepository
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItemFactory $feedItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     */
    public function __construct(
        protected readonly LuigisBoxProductRepository $luigisBoxProductRepository,
        protected readonly LuigisBoxFeedItemFactory $feedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItem[]|iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->luigisBoxProductRepository->getProducts(
            $domainConfig,
            $pricingGroup,
            $lastSeekId,
            $maxResults,
        );
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);

        foreach ($products as $product) {
            yield $this->feedItemFactory->create($product, $domainConfig);
        }
    }
}
