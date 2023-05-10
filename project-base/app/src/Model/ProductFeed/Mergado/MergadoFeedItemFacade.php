<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado;

use App\Model\ProductFeed\Mergado\Exception\MissingRequiredInformationException;
use App\Model\ProductFeed\Mergado\FeedItem\MergadoFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;

class MergadoFeedItemFacade
{
    /**
     * @var \App\Model\ProductFeed\Mergado\MergadoProductRepository
     */
    private $mergadoProductRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader
     */
    private $productUrlsBatchLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader
     */
    private $productParametersBatchLoader;

    /**
     * @var \App\Model\ProductFeed\Mergado\FeedItem\MergadoFeedItemFactory
     */
    private $mergadoFeedItemFactory;

    /**
     * @param \App\Model\ProductFeed\Mergado\MergadoProductRepository $mergadoProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader
     * @param \App\Model\ProductFeed\Mergado\FeedItem\MergadoFeedItemFactory $mergadoFeedItemFactory
     */
    public function __construct(
        MergadoProductRepository $mergadoProductRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        ProductUrlsBatchLoader $productUrlsBatchLoader,
        ProductParametersBatchLoader $productParametersBatchLoader,
        MergadoFeedItemFactory $mergadoFeedItemFactory
    ) {
        $this->mergadoProductRepository = $mergadoProductRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->productUrlsBatchLoader = $productUrlsBatchLoader;
        $this->productParametersBatchLoader = $productParametersBatchLoader;
        $this->mergadoFeedItemFactory = $mergadoFeedItemFactory;
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
            try {
                yield $this->mergadoFeedItemFactory->createForProduct($product, $domainConfig);
            } catch (MissingRequiredInformationException $exception) {
                //skip single item
            }
        }
    }
}
