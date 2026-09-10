<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductRepository;

class GoogleFeedItemFacade
{
    public function __construct(
        protected readonly GoogleProductRepository $googleProductRepository,
        protected readonly GoogleFeedItemFactory $feedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductAdditionalServicesBatchLoader $productAdditionalServicesBatchLoader,
    ) {
    }

    /**
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem[]|iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->googleProductRepository->getProducts(
            $domainConfig,
            $pricingGroup,
            $lastSeekId,
            $maxResults,
        );
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);
        $this->productAdditionalServicesBatchLoader->loadShownInFeedsForProducts($products, $domainConfig);

        foreach ($products as $product) {
            yield $this->feedItemFactory->create($product, $domainConfig);
        }
    }
}
