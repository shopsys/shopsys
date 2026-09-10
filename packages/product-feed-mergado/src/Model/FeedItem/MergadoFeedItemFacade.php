<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\ProductFeed\MergadoBundle\Model\Product\MergadoProductRepository;

class MergadoFeedItemFacade
{
    public function __construct(
        protected readonly MergadoProductRepository $mergadoProductRepository,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly MergadoFeedItemFactory $mergadoFeedItemFactory,
        protected readonly ProductAdditionalServicesBatchLoader $productAdditionalServicesBatchLoader,
    ) {
    }

    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->mergadoProductRepository->getProducts($domainConfig, $pricingGroup, $lastSeekId, $maxResults);
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);
        $this->productParametersBatchLoader->loadForProducts($products, $domainConfig);
        $this->productAdditionalServicesBatchLoader->loadShownInFeedsForProducts($products, $domainConfig);

        foreach ($products as $product) {
            yield $this->mergadoFeedItemFactory->createForProduct($product, $domainConfig);
        }
    }
}
