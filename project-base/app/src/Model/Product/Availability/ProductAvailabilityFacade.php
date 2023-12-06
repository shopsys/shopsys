<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use App\Component\Setting\Setting;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Symfony\Contracts\Service\ResetInterface;

class ProductAvailabilityFacade implements ResetInterface
{
    private const DAYS_IN_WEEK = 7;

    /**
     * @var array<string, bool>
     */
    private array $productAvailabilityDomainCache = [];

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade $productStockFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly Setting $setting,
        private readonly ProductStockFacade $productStockFacade,
        private readonly StoreFacade $storeFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityInformationByDomainId(Product $product, int $domainId): string
    {
        $domainLocale = $this->domain->getDomainConfigById($domainId)->getLocale();

        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return t('In stock', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale);
        }

        return t('Out of stock', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int|null
     */
    public function getProductAvailabilityDaysByDomainId(Product $product, int $domainId): ?int
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return 0;
        }

        return null;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Availability\AvailabilityStatusEnum
     */
    public function getProductAvailabilityStatusByDomainId(Product $product, int $domainId): AvailabilityStatusEnum
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return AvailabilityStatusEnum::InStock;
        }

        return AvailabilityStatusEnum::OutOfStock;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailableStoresCountInformationByDomainId(Product $product, int $domainId): string
    {
        $count = $this->getAvailableStoresCount($product, $domainId);

        return t(
            '{0}|{1}Available in <span class="box-detail__avail__text__strong">%count%</span> store|[2,Inf]Available in <span class="box-detail__avail__text__strong">%count%</span> stores',
            ['%count%' => $count],
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function getAvailableStoresCount(Product $product, int $domainId): int
    {
        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);

        $count = 0;

        foreach ($productStocks as $productStock) {
            if ($productStock->getProductQuantity() > 0 && $productStock->getStock()->isEnabled($domainId)) {
                $count += count($productStock->getStock()->getStores());
            }
        }

        return $count;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductAvailableOnDomainCached(Product $product, int $domainId): bool
    {
        $cacheKey = sprintf('product:%d-domain:%d', $product->getId(), $domainId);

        if (array_key_exists($cacheKey, $this->productAvailabilityDomainCache)) {
            return $this->productAvailabilityDomainCache[$cacheKey];
        }

        $this->productAvailabilityDomainCache[$cacheKey] = $this->productStockFacade->isProductAvailableOnDomain($product, $domainId);

        return $this->productAvailabilityDomainCache[$cacheKey];
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Availability\ProductStoreAvailabilityInformation[]
     */
    public function getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId(
        Product $product,
        int $domainId,
    ): array {
        $stores = $this->storeFacade->getStoresListEnabledOnDomain($domainId);

        $isAvailable = $this->isProductAvailableOnDomainCached($product, $domainId);

        $productStocksIndexedByStockId = $this->productStockFacade->getProductStocksByProductIndexedByStockId($product);

        $productStoresAvailabilityInformationList = [];

        $domainLocale = $this->domain->getDomainConfigById($domainId)->getLocale();

        foreach ($stores as $store) {
            $availabilityStatus = AvailabilityStatusEnum::InStock;
            $availabilityInformation = t('Available immediately', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale);

            if (!$isAvailable) {
                $availabilityStatus = AvailabilityStatusEnum::OutOfStock;
                $availabilityInformation = t('Unavailable', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $domainLocale);
            } else {
                $stock = $store->getStock();

                $productStock = null;

                if ($stock !== null && $stock->isEnabled($domainId)) {
                    $productStock = $productStocksIndexedByStockId[$stock->getId()];
                }

                if ($productStock === null || $productStock->getProductQuantity() <= 0) {
                    $weeks = $this->getTransferWeeksByDomainId($domainId);
                    $availabilityInformation = $this->getWeeksAvailabilityMessageByWeeks($weeks, $domainId);
                }
            }

            $productStoresAvailabilityInformationList[$store->getId()] = new ProductStoreAvailabilityInformation(
                $store->getName(),
                $store->getId(),
                $availabilityInformation,
                $availabilityStatus,
            );
        }

        return $productStoresAvailabilityInformationList;
    }

    /**
     * @param int $weeks
     * @param int $domainId
     * @return string
     */
    private function getWeeksAvailabilityMessageByWeeks(int $weeks, int $domainId): string
    {
        $domainLocale = $this->domain->getDomainConfigById($domainId)->getLocale();

        return t(
            '{0,1} Available in one week|[2,Inf] Available in %count% weeks',
            ['%count%' => $weeks],
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            $domainLocale,
        );
    }

    /**
     * @param int $days
     * @return int
     */
    public static function calculateDaysToWeeks(int $days): int
    {
        return (int)ceil($days / self::DAYS_IN_WEEK);
    }

    /**
     * @param int $domainId
     * @return int
     */
    private function getTransferWeeksByDomainId(int $domainId): int
    {
        return self::calculateDaysToWeeks($this->getTransferDaysByDomainId($domainId));
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getTransferDaysByDomainId(int $domainId): int
    {
        return $this->setting->getForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function getGroupedStockQuantityByProductAndDomainId(Product $product, int $domainId): int
    {
        $productStocksByDomainIdIndexedByStockId = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId($product, $domainId);

        return $this->sumProductStockQuantities($productStocksByDomainIdIndexedByStockId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStock[] $productStocksByDomainIdIndexedByStockId
     * @return int
     */
    private function sumProductStockQuantities(array $productStocksByDomainIdIndexedByStockId): int
    {
        $totalProductStocksQuantity = 0;

        foreach ($productStocksByDomainIdIndexedByStockId as $productStock) {
            $totalProductStocksQuantity += $productStock->getProductQuantity();
        }

        return $totalProductStocksQuantity;
    }

    public function reset(): void
    {
        $this->productAvailabilityDomainCache = [];
    }
}
