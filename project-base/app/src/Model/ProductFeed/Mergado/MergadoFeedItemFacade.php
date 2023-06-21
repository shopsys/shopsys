<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado;

use App\Model\ProductFeed\Mergado\FeedItem\MergadoFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;

class MergadoFeedItemFacade
{
    /**
     * @param \App\Model\ProductFeed\Mergado\MergadoProductRepository $mergadoProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader
     * @param \App\Model\ProductFeed\Mergado\FeedItem\MergadoFeedItemFactory $mergadoFeedItemFactory
     */
    public function __construct(
        private readonly MergadoProductRepository $mergadoProductRepository,
        private readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        private readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        private readonly ProductParametersBatchLoader $productParametersBatchLoader,
        private readonly MergadoFeedItemFactory $mergadoFeedItemFactory,
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
        /** @var \App\Model\Product\Product[] $products */
        $products = $this->mergadoProductRepository->getProducts($domainConfig, $pricingGroup, $lastSeekId, $maxResults);
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);
        $this->productParametersBatchLoader->loadForProducts($products, $domainConfig);

        foreach ($products as $product) {
            yield $this->mergadoFeedItemFactory->createForProduct($product, $domainConfig);
        }
    }
}
