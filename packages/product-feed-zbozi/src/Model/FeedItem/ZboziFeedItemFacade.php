<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainFacade;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductRepository;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryFacade;

class ZboziFeedItemFacade
{
    public function __construct(
        protected readonly ZboziProductRepository $zboziProductRepository,
        protected readonly ZboziFeedItemFactory $feedItemFactory,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly ZboziProductDomainFacade $zboziProductDomainFacade,
        protected readonly ZboziCategoryFacade $zboziCategoryFacade,
        protected readonly ProductAdditionalServicesBatchLoader $productAdditionalServicesBatchLoader,
    ) {
    }

    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $products = $this->zboziProductRepository->getProducts($domainConfig, $pricingGroup, $lastSeekId, $maxResults);
        $this->productUrlsBatchLoader->loadForProducts($products, $domainConfig);
        $this->productParametersBatchLoader->loadForProducts($products, $domainConfig);
        $this->productAdditionalServicesBatchLoader->loadShownInFeedsForProducts($products, $domainConfig);

        $zboziProductDomains = $this->zboziProductDomainFacade->getZboziProductDomainsByProductsAndDomainIndexedByProductId(
            $products,
            $domainConfig,
        );
        $zboziCategoryTextsByProductId = $this->zboziCategoryFacade->getFullNamesByProductsIndexedByProductId(
            $products,
            $domainConfig,
        );

        foreach ($products as $product) {
            $zboziProductDomain = $zboziProductDomains[$product->getId()] ?? null;
            $categoryText = $zboziCategoryTextsByProductId[$product->getId()] ?? null;

            yield $this->feedItemFactory->create(
                $product,
                $zboziProductDomain,
                $domainConfig,
                $categoryText,
            );
        }
    }
}
