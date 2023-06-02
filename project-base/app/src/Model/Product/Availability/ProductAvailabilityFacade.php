<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use App\Component\Setting\Setting;
use App\Model\Product\Product;
use App\Model\Stock\ProductStock;
use App\Model\Stock\ProductStockFacade;
use App\Model\Store\ProductStoreFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

class ProductAvailabilityFacade
{
    private const DAYS_IN_WEEK = 7;

    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private ProductStockFacade $productStockFacade;

    /**
     * @var \App\Component\Setting\Setting
     */
    private Setting $setting;

    /**
     * @var array
     */
    private $productAvailabilityDomainCache;

    /**
     * @var \App\Model\Store\ProductStoreFacade
     */
    private ProductStoreFacade $productStoreFacade;

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \App\Model\Store\ProductStoreFacade $productStoreFacade
     */
    public function __construct(
        Setting $setting,
        ProductStockFacade $productStockFacade,
        ProductStoreFacade $productStoreFacade
    ) {
        $this->productStockFacade = $productStockFacade;
        $this->setting = $setting;
        $this->productAvailabilityDomainCache = [];
        $this->productStoreFacade = $productStoreFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityInformationByDomainId(Product $product, int $domainId): string
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return t('In stock');
        }

        if ($product->hasPreorder() === false) {
            return t('Vyprodáno');
        }

        return $this->getDeliveryWeeksAvailabilityMessageByProductAndDomainId($product, $domainId);
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

        if ($product->hasPreorder() === false) {
            return null;
        }

        return $this->getDeliveryDaysByDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function getDeliveryWeeksAvailabilityMessageByProductAndDomainId(Product $product, int $domainId): string
    {
        $weeks = $this->getDeliveryWeeksByDomainId($domainId, $product);

        return $this->getWeeksAvailabilityMessageByWeeks($weeks);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityInformationByQuantifiedProductAndDomainId(QuantifiedProduct $quantifiedProduct, int $domainId): string
    {
        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();
        $productStocks = $this->productStockFacade->getProductStocksByProductAndDomainId($product, $domainId);

        $groupedStockQuantity = $this->sumProductStockQuantities($productStocks);
        if ($groupedStockQuantity >= $quantifiedProduct->getQuantity()) {
            return t('In stock');
        }

        if ($product->hasPreorder() === false) {
            return t('Vyprodáno');
        }

        return $this->getDeliveryWeeksAvailabilityMessageByProductAndDomainId($product, $domainId);
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
            '{0}|{1}Můžete mít ihned na <span class="box-detail__avail__text__strong">%count%</span> prodejně|[2,Inf]Můžete mít ihned na <span class="box-detail__avail__text__strong">%count%</span> prodejnách',
            ['%count%' => $count]
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

        return  $count;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductCountExposedInStoresInformationByDomainId(Product $product, int $domainId): string
    {
        $count = $this->getExposedStoresCount($product, $domainId);

        return t(
            '{0}|{1}Můžete si prohlédnout na <span class="box-detail__avail__text__strong">%count%</span> prodejně|[2,Inf]Můžete si prohlédnout na <span class="box-detail__avail__text__strong">%count%</span> prodejnách',
            ['%count%' => $count]
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function getExposedStoresCount(Product $product, int $domainId): int
    {
        $productStores = $this->productStoreFacade->getProductStoresByProductAndDomainId($product, $domainId);

        $count = 0;
        foreach ($productStores as $productStore) {
            if ($productStore->isProductExposed()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function calculateProductAvailabilityDaysForDomainId(Product $product, int $domainId): int
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return 0;
        }

        return $this->getDeliveryDaysByDomainId($product, $domainId);
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
     * @return bool
     */
    public function isProductExcludedOnDomain(Product $product, int $domainId): bool
    {
        return $product->getSaleExclusion($domainId) && !$this->isProductAvailableOnDomainCached(
            $product,
            $domainId
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductAvailableOnDomainOrHasPreorder(Product $product, int $domainId): bool
    {
        return $product->hasPreorder() || $this->isProductAvailableOnDomainCached(
            $product,
            $domainId
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Availability\ProductStoreAvailabilityInformation[]
     */
    public function getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId(Product $product, int $domainId): array
    {
        $productStores = $this->productStoreFacade->getProductStoresByProductAndDomainId($product, $domainId);

        $weeks = $this->getDeliveryWeeksByDomainId($domainId, $product);
        $isOutOfStock = true;
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            $weeks = $this->getTransferWeeksByDomainId($domainId);
            $isOutOfStock = false;
        }

        $productStocksIndexedByStockId = $this->productStockFacade->getProductStocksByProductIndexedByStockId($product);

        $productStoresAvailabilityInformationList = [];
        foreach ($productStores as $productStore) {
            $availabilityInformation = t('Ihned k odběru');
            $availabilityStatus = AvailabilityStatusEnum::InStock;

            if ($isOutOfStock) {
                $availabilityStatus = AvailabilityStatusEnum::OutOfStock;
                $availabilityInformation = t('Unavailable');
            } else {
                $stock = $productStore->getStore()->getStock();

                $productStock = null;
                if ($stock !== null && $stock->isEnabled($domainId)) {
                    $productStock = $productStocksIndexedByStockId[$stock->getId()];
                }

                if ($productStock === null || $productStock->getProductQuantity() <= 0) {
                    $availabilityInformation = $this->getWeeksAvailabilityMessageByWeeks($weeks);
                }
            }

            $productStoresAvailabilityInformationList[$productStore->getStore()->getId()] = new ProductStoreAvailabilityInformation(
                $productStore->getStore()->getName(),
                $productStore->getStore()->getId(),
                $availabilityInformation,
                $productStore->isProductExposed(),
                $availabilityStatus
            );
        }

        return $productStoresAvailabilityInformationList;
    }

    /**
     * @param int $weeks
     * @return string
     */
    private function getWeeksAvailabilityMessageByWeeks(int $weeks): string
    {
        return t(
            '{0,1} K dispozici za týden|[2,4] K dispozici za %count% týdny|[5,Inf] K dispozici za %count% týdnů',
            ['%count%' => $weeks]
        );
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product $product
     * @return int
     */
    private function getDeliveryWeeksByDomainId(int $domainId, Product $product): int
    {
        return self::calculateDaysToWeeks($this->getDeliveryDaysByDomainId($product, $domainId));
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
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function getDeliveryDaysByDomainId(Product $product, int $domainId): int
    {
        $deliveryDays = $this->setting->getForDomain(Setting::DELIVERY_DAYS_ON_STOCK, $domainId);
        $deliveryDays += $product->getVendorDeliveryDate() ?? 0;

        return $deliveryDays;
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
    public function getShippingDaysByDomainId(Product $product, int $domainId): int
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return $this->getTransferDaysByDomainId($domainId);
        }
        return $this->getDeliveryDaysByDomainId($product, $domainId);
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
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function getMaximumOrderQuantity(Product $product, int $domainId): int
    {
        if ($product->hasPreorder()) {
            return PHP_INT_MAX;
        }

        return $this->getGroupedStockQuantityByProductAndDomainId($product, $domainId);
    }

    /**
     * @param int $domainId
     * @param \App\Model\Store\Store[] $stores
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return int[]
     */
    public function getStoreDayAvailabilitiesIndexedByStoreId(int $domainId, array $stores, array $quantifiedProducts): array
    {
        $maximumDayAvailabilityByStoreId = [];
        foreach ($stores as $store) {
            $maximumDayAvailabilityByStoreId[$store->getId()] = 0;
        }

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $maximumDayAvailabilityByStoreId = $this->getMaximumDayAvailabilityForProductIndexedByStockId(
                $quantifiedProduct,
                $maximumDayAvailabilityByStoreId,
                $domainId,
                $stores
            );
        }

        return $maximumDayAvailabilityByStoreId;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Store\Store[] $stores
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Transport\Transport[] $transports
     * @return int[]
     */
    public function getMinimalDaysAvailabilityIndexedByTransportIds(
        int $domainId,
        array $stores,
        array $quantifiedProducts,
        array $transports
    ): array {
        $storeDayAvailabilities = $this->getStoreDayAvailabilitiesIndexedByStoreId($domainId, $stores, $quantifiedProducts);
        asort($storeDayAvailabilities);

        $minimalStockDaysAvailability = reset($storeDayAvailabilities);

        $minimalDaysAvailabilityIndexedByTransportIds = [];
        foreach ($transports as $transport) {
            $minimalDaysAvailabilityIndexedByTransportIds[$transport->getId()] = $minimalStockDaysAvailability + $transport->getDaysUntilDelivery();
        }

        return $minimalDaysAvailabilityIndexedByTransportIds;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int[] $maximumDayAvailabilityByStoreId
     * @param int $domainId
     * @param \App\Model\Store\Store[] $stores
     * @return int[]
     */
    private function getMaximumDayAvailabilityForProductIndexedByStockId(
        QuantifiedProduct $quantifiedProduct,
        array $maximumDayAvailabilityByStoreId,
        int $domainId,
        array $stores
    ): array {
        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();

        $productStocksByDomainIdIndexedByStockId = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId(
            $product,
            $domainId
        );

        $productStocksFromStoresIndexedByStoreId = [];
        foreach ($stores as $store) {
            $stockFromStore = $store->getStock();
            if ($stockFromStore !== null && array_key_exists($stockFromStore->getId(), $productStocksByDomainIdIndexedByStockId)) {
                $productStocksFromStoresIndexedByStoreId[$store->getId()] = $productStocksByDomainIdIndexedByStockId[$stockFromStore->getId()];
            }
        }

        $quantityOnAllStocks = $this->sumProductStockQuantities($productStocksFromStoresIndexedByStoreId);

        foreach ($maximumDayAvailabilityByStoreId as $storeId => $maximumDayAvailability) {
            $productDayAvailability = $this->getDayAvailabilityForProductAndStock(
                $quantifiedProduct,
                $productStocksFromStoresIndexedByStoreId[$storeId] ?? null,
                $quantityOnAllStocks,
                $domainId
            );

            $maximumDayAvailabilityByStoreId[$storeId] = max(
                $maximumDayAvailability,
                $productDayAvailability
            );
        }

        return $maximumDayAvailabilityByStoreId;
    }

    /**
     * @param \App\Model\Stock\ProductStock[] $productStocksByDomainIdIndexedByStockId
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \App\Model\Stock\ProductStock|null $productStock
     * @param int $quantityOnAllStocks
     * @param int $domainId
     * @return int
     */
    private function getDayAvailabilityForProductAndStock(
        QuantifiedProduct $quantifiedProduct,
        ?ProductStock $productStock,
        int $quantityOnAllStocks,
        int $domainId
    ): int {
        //php_int_max serves as a numerical indicator of unavailability of goods
        $productBetweenStockTransferDays = PHP_INT_MAX;
        if ($quantityOnAllStocks >= $quantifiedProduct->getQuantity()) {
            $productBetweenStockTransferDays = $this->getTransferDaysByDomainId($domainId);
        }

        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();

        //relation between product and stock doesn't exists
        if ($productStock === null) {
            $defaultVendorDeliveryDays = $this->getDeliveryDaysByDomainId($product, $domainId);
            return min($defaultVendorDeliveryDays, $productBetweenStockTransferDays);
        }

        //the product is in the stock
        $quantityOnStock = $productStock->getProductQuantity();
        if ($quantityOnStock >= $quantifiedProduct->getQuantity()) {
            return 0;
        }

        //we choose whether it is faster to transfer the product from other stocks
        if ($productBetweenStockTransferDays < PHP_INT_MAX) {
            return $productBetweenStockTransferDays;
        }

        return $this->getDeliveryDaysByDomainId($product, $domainId);
    }
}
