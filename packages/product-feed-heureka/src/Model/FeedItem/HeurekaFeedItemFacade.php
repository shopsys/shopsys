<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductRepository;

class HeurekaFeedItemFacade
{
    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductRepository
     */
    protected $heurekaProductRepository;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory
     */
    protected $feedItemFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaProductDataBatchLoader
     */
    protected $productDataBatchLoader;

    public function __construct(
        HeurekaProductRepository $heurekaProductRepository,
        HeurekaFeedItemFactory $feedItemFactory,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        HeurekaProductDataBatchLoader $productDataBatchLoader
    ) {
        $this->heurekaProductRepository = $heurekaProductRepository;
        $this->feedItemFactory = $feedItemFactory;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->productDataBatchLoader = $productDataBatchLoader;
    }

    /**
     * @param int|null $lastSeekId
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItem[]|iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->heurekaProductRepository->getProducts($domainConfig, $pricingGroup, $lastSeekId, $maxResults);
        $this->productDataBatchLoader->loadForProducts($products, $domainConfig);

        foreach ($products as $product) {
            yield $this->feedItemFactory->create($product, $domainConfig);
        }
    }
}
