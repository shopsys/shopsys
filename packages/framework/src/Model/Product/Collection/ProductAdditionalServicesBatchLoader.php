<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Collection;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductAdditionalServicesNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAdditionalServicesBatchLoader
{
    protected const string PRODUCT_ADDITIONAL_SERVICES_CACHE_NAMESPACE = 'loadedProductAdditionalServicesShownInFeeds';

    protected const int SPECIAL_SERVICES_MAX_COUNT = 5;

    public function __construct(
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function loadShownInFeedsForProducts(array $products, DomainConfig $domainConfig): void
    {
        $additionalServicesByProductId = $this->additionalServiceFacade->getShownInFeedsIndexedByProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
            $domainConfig->getId(),
        );

        foreach ($products as $product) {
            $key = $this->getKey($product, $domainConfig);

            $this->inMemoryCache->save(
                static::PRODUCT_ADDITIONAL_SERVICES_CACHE_NAMESPACE,
                $additionalServicesByProductId[$product->getId()] ?? [],
                $key,
            );
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getShownInFeedsAdditionalServices(Product $product, DomainConfig $domainConfig): array
    {
        $key = $this->getKey($product, $domainConfig);

        if ($this->inMemoryCache->hasItem(static::PRODUCT_ADDITIONAL_SERVICES_CACHE_NAMESPACE, $key)) {
            return $this->inMemoryCache->getItem(static::PRODUCT_ADDITIONAL_SERVICES_CACHE_NAMESPACE, $key);
        }

        throw new ProductAdditionalServicesNotLoadedException($product, $domainConfig);
    }

    /**
     * @return string[]
     */
    public function getShownInFeedsFeedNames(Product $product, DomainConfig $domainConfig): array
    {
        $feedNames = [];

        foreach ($this->getShownInFeedsAdditionalServices($product, $domainConfig) as $additionalService) {
            $feedName = $additionalService->getFeedName($domainConfig->getLocale());

            if ($feedName === null) {
                continue;
            }

            $feedNames[] = $feedName;
        }

        return $feedNames;
    }

    /**
     * @return string[]
     */
    public function getShownInFeedsSpecialServiceNames(Product $product, DomainConfig $domainConfig): array
    {
        return array_slice(
            $this->getShownInFeedsFeedNames($product, $domainConfig),
            0,
            static::SPECIAL_SERVICES_MAX_COUNT,
        );
    }

    /**
     * @return array<int, array{extraMessage: string, customText: string|null}>
     */
    public function getShownInFeedsZboziEntries(Product $product, DomainConfig $domainConfig): array
    {
        $zboziEntries = [];

        foreach ($this->getShownInFeedsAdditionalServices($product, $domainConfig) as $additionalService) {
            $zboziServiceType = $additionalService->getZboziServiceType();

            if ($zboziServiceType === null) {
                continue;
            }

            $zboziEntries[] = [
                'extraMessage' => $zboziServiceType,
                'customText' => $additionalService->getZboziDescription($domainConfig->getLocale()),
            ];
        }

        return $zboziEntries;
    }

    protected function getKey(Product $product, DomainConfig $domainConfig): string
    {
        return $domainConfig->getId() . '-' . $product->getId();
    }
}
