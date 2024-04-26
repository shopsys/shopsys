<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\Product\LuigisBoxProductRepository;

class LuigisBoxProductFeedItemFacade
{
    /**
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\Model\Product\LuigisBoxProductRepository $luigisBoxProductRepository
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItemFactory $luigisBoxProductFeedItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     */
    public function __construct(
        protected readonly LuigisBoxProductRepository $luigisBoxProductRepository,
        protected readonly LuigisBoxProductFeedItemFactory $luigisBoxProductFeedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return iterable<int, \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem>
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
            if ($product->isMainVariant()) {
                $variants = $this->luigisBoxProductRepository->getAllVariantsByMainVariantId($product, $domainConfig->getId(), $pricingGroup);
                $this->productUrlsBatchLoader->loadForProducts($variants, $domainConfig);

                foreach ($variants as $variant) {
                    yield $this->luigisBoxProductFeedItemFactory->create($variant, $domainConfig);
                }
            } else {
                yield $this->luigisBoxProductFeedItemFactory->create($product, $domainConfig);
            }
        }
    }
}
