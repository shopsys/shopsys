<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use App\Component\Setting\Setting;
use App\Model\Product\Product;
use App\Model\Stock\ProductStock;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\ProductStockRepository;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

class ProductAvailabilityFacade
{
    private const DAYS_IN_WEEK = 7;

    private const AVAILABILITY_STATUS_IN_STOCK = 'in-stock';
    private const AVAILABILITY_STATUS_OUT_OF_STOCK = 'out-of-stock';

    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private $productStockFacade;

    /**
     * @var \App\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \App\Model\Stock\ProductStockRepository
     */
    private $productStockRepository;

    /**
     * @var array
     */
    private $productAvailabilityDomainCache;

    /**
     * @var array
     */
    private $productFutureAvailabilityDomainCache;

    /**
     * @param \App\Model\Stock\ProductStockRepository $productStockRepository
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     */
    public function __construct(
        ProductStockRepository $productStockRepository,
        Setting $setting,
        ProductStockFacade $productStockFacade
    ) {
        $this->productStockFacade = $productStockFacade;
        $this->setting = $setting;
        $this->productStockRepository = $productStockRepository;
        $this->productAvailabilityDomainCache = [];
        $this->productFutureAvailabilityDomainCache = [];
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityInformationByDomainId(Product $product, int $domainId): string
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return t('Skladem');
        }

        $productStocks = $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);
        $closestFutureProductStock = $this->getClosestFutureProductStockByDomainId($productStocks);
        if ($closestFutureProductStock !== null) {
            return $this->getFutureWeeksAvailabilityByClosestFutureProductStockAndDomainId($closestFutureProductStock, $domainId);
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

        $productStocks = $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);
        $closestFutureProductStock = $this->getClosestFutureProductStockByDomainId($productStocks);
        if ($closestFutureProductStock !== null) {
            return $this->getFutureDaysByClosestFutureProductStockAndDomainId($closestFutureProductStock, $domainId);
        }

        if ($product->hasPreorder() === false) {
            return null;
        }

        return $this->getDeliveryDaysByDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Stock\ProductStock $closestFutureProductStock
     * @param int $domainId
     * @return string
     */
    private function getFutureWeeksAvailabilityByClosestFutureProductStockAndDomainId(ProductStock $closestFutureProductStock, int $domainId): string
    {
        $futureStockAvailabilityDays = $this->getFutureDaysByClosestFutureProductStockAndDomainId($closestFutureProductStock, $domainId);

        return $this->getWeeksAvailabilityMessageByDays($futureStockAvailabilityDays);
    }

    /**
     * @param \App\Model\Stock\ProductStock $closestFutureProductStock
     * @param int $domainId
     * @return int
     */
    private function getFutureDaysByClosestFutureProductStockAndDomainId(ProductStock $closestFutureProductStock, int $domainId): int
    {
        $futureStockAvailabilityDays = $this->getFutureStockAvailabilityDaysByDomainId($closestFutureProductStock, $domainId);
        if ($closestFutureProductStock->getStock()->isCentralStock()) {
            $futureStockAvailabilityDays += $this->getTransferDaysByDomainId($domainId);
        }

        return $futureStockAvailabilityDays;
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
        $productStocks = $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);

        $groupedStockQuantity = $this->sumProductStockQuantities($productStocks);
        if ($groupedStockQuantity >= $quantifiedProduct->getQuantity()) {
            return t('Skladem');
        }
        $requiredFutureProductQuantity = $quantifiedProduct->getQuantity() - $groupedStockQuantity;

        $closestFutureProductStock = $this->getClosestFutureProductStockByDomainId($productStocks, $requiredFutureProductQuantity);
        if ($closestFutureProductStock !== null) {
            return $this->getFutureWeeksAvailabilityByClosestFutureProductStockAndDomainId($closestFutureProductStock, $domainId);
        }

        if ($product->hasPreorder() === false) {
            return t('Vyprodáno');
        }

        return $this->getDeliveryWeeksAvailabilityMessageByProductAndDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityStatusByDomainId(Product $product, int $domainId): string
    {
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            return self::AVAILABILITY_STATUS_IN_STOCK;
        }

        return self::AVAILABILITY_STATUS_OUT_OF_STOCK;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailableStocksCountInformationByDomainId(Product $product, int $domainId): string
    {
        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);

        $count = 0;
        foreach ($productStocks as $productStock) {
            if ($productStock->getStock()->getDomainId() === $domainId
                && $productStock->getStock()->isCentralStock() === false
                && $productStock->getProductQuantity() > 0
            ) {
                $count++;
            }
        }

        return tc(
            '{0}|{1}Můžete mít ihned na <span class="box-detail__avail__text__strong">%count%</span> prodejně|[2,Inf]Můžete mít ihned na <span class="box-detail__avail__text__strong">%count%</span> prodejnách',
            $count,
            ['%count%' => $count]
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductCountExposedInStocksInformationByDomainId(Product $product, int $domainId): string
    {
        $productStocks = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId($product, $domainId);

        $count = 0;
        foreach ($productStocks as $productStock) {
            if ($productStock->getStock()->isCentralStock() === false
                && $productStock->isProductExposed()
            ) {
                $count++;
            }
        }

        return tc(
            '{0}|{1}Můžete si prohlédnout na <span class="box-detail__avail__text__strong">%count%</span> prodejně|[2,Inf]Můžete si prohlédnout na <span class="box-detail__avail__text__strong">%count%</span> prodejnách',
            $count,
            ['%count%' => $count]
        );
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

        $this->productAvailabilityDomainCache[$cacheKey] = $this->productStockRepository->isProductAvailableOnDomain($product, $domainId);

        return $this->productAvailabilityDomainCache[$cacheKey];
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductFutureAvailableOnDomainCached(Product $product, int $domainId): bool
    {
        $cacheKey = sprintf('product:%d-domain:%d', $product->getId(), $domainId);
        if (array_key_exists($cacheKey, $this->productFutureAvailabilityDomainCache)) {
            return $this->productFutureAvailabilityDomainCache[$cacheKey];
        }

        $this->productFutureAvailabilityDomainCache[$cacheKey] = $this->productStockRepository->isProductAvailableByFutureStockOnDomain($product, $domainId);

        return $this->productFutureAvailabilityDomainCache[$cacheKey];
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
     * @return bool
     */
    public function isProductAvailableWithFutureStockOnDomainOrHasPreorder(Product $product, int $domainId): bool
    {
        return $product->hasPreorder()
            ||
            $this->isProductAvailableOnDomainCached(
                $product,
                $domainId
            )
            ||
            $this->isProductFutureAvailableOnDomainCached(
                $product,
                $domainId
            );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Availability\ProductStockAvailabilityInformation[]
     */
    public function getProductStocksAvailabilitiesInformationByDomainIdIndexedByStockId(Product $product, int $domainId): array
    {
        $productStocks = $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);

        $weeks = $this->getDeliveryWeeksByDomainId($domainId, $product);
        $isOutOfStock = true;
        if ($this->isProductAvailableOnDomainCached($product, $domainId)) {
            $weeks = $this->getTransferWeeksByDomainId($domainId);
            $isOutOfStock = false;
        }

        $outOfStockAvailabilityInformation = $this->getWeeksAvailabilityMessageByWeeks($weeks);

        if ($product->hasPreorder() === false) {
            $outOfStockAvailabilityInformation = t('Vyprodáno');
        }

        $closestFutureProductStock = $this->getClosestFutureProductStockByDomainId($productStocks);
        if ($closestFutureProductStock !== null) {
            $closestFutureStockAvailabilityWeeksForOtherStocks = $this->getClosestStockAvailabilityWeeksForOtherStocksByDomainId(
                $this->getFutureStockAvailabilityDaysByDomainId($closestFutureProductStock, $domainId),
                $domainId
            );
            $closestFutureStockAvailabilityInformationForOtherStocks = $this->getWeeksAvailabilityMessageByWeeks($closestFutureStockAvailabilityWeeksForOtherStocks);
        } else {
            $closestFutureStockAvailabilityInformationForOtherStocks = $outOfStockAvailabilityInformation;
        }

        $productStocksAvailabilityInformationList = [];
        foreach ($productStocks as $productStock) {
            if ($productStock->getStock()->isCentralStock()) {
                continue;
            }

            $availabilityInformation = t('Můžete mít <strong class="is-in-stock">ihned</strong>');
            $availabilityStatus = self::AVAILABILITY_STATUS_IN_STOCK;

            if ($isOutOfStock) {
                if ($productStock->getDateOfStorage() !== null) {
                    $futureStockAvailabilityWeeks = $this->getFutureStockAvailabilityWeeksByDomainId($productStock, $domainId);
                    $availabilityInformation = $this->getWeeksAvailabilityMessageByWeeks($futureStockAvailabilityWeeks);
                } else {
                    $availabilityInformation = $closestFutureStockAvailabilityInformationForOtherStocks;
                }
                $availabilityStatus = self::AVAILABILITY_STATUS_OUT_OF_STOCK;
            } else {
                if ($productStock->getProductQuantity() <= 0) {
                    $availabilityInformation = $outOfStockAvailabilityInformation;
                    $availabilityStatus = self::AVAILABILITY_STATUS_OUT_OF_STOCK;
                }
            }

            $productStocksAvailabilityInformationList[$productStock->getStock()->getId()] = new ProductStockAvailabilityInformation(
                $productStock->getStock()->getName(),
                $productStock->getStock()->getId(),
                $availabilityInformation,
                $productStock->isProductExposed(),
                $availabilityStatus
            );
        }

        return $productStocksAvailabilityInformationList;
    }

    /**
     * @param \App\Model\Stock\ProductStock[] $productStocks
     * @param int $minimalProductQuantity
     * @return \App\Model\Stock\ProductStock|null
     */
    private function getClosestFutureProductStockByDomainId(array $productStocks, int $minimalProductQuantity = 0): ?ProductStock
    {
        $productStocksIndexedByDaysUntilStorage = [];
        foreach ($productStocks as $productStock) {
            if ($productStock->getDateOfStorage() !== null) {
                $productStocksIndexedByDaysUntilStorage[$productStock->getDaysUntilStorage()] = $productStock;
            }
        }
        ksort($productStocksIndexedByDaysUntilStorage);

        $sumProductFutureQuantity = 0;
        foreach ($productStocksIndexedByDaysUntilStorage as $productStock) {
            $sumProductFutureQuantity += $productStock->getFutureProductQuantity();

            if ($sumProductFutureQuantity >= $minimalProductQuantity) {
                return $productStock;
            }
        }

        return null;
    }

    /**
     * @param \App\Model\Stock\ProductStock $dateOfStorage
     * @param int $domainId
     * @return int
     */
    private function getFutureStockAvailabilityWeeksByDomainId(ProductStock $dateOfStorage, int $domainId): int
    {
        return self::calculateDaysToWeeks($this->getFutureStockAvailabilityDaysByDomainId($dateOfStorage, $domainId));
    }

    /**
     * @param \App\Model\Stock\ProductStock $productStock
     * @param int $domainId
     * @return int
     */
    private function getFutureStockAvailabilityDaysByDomainId(ProductStock $productStock, int $domainId): int
    {
        return $productStock->getDaysUntilStorage() + $this->getFutureStorageReservationByDomainId($domainId);
    }

    /**
     * @param int $closestFutureAvailabilityDays
     * @param int $domainId
     * @return int
     */
    private function getClosestStockAvailabilityWeeksForOtherStocksByDomainId(int $closestFutureAvailabilityDays, int $domainId): int
    {
        return self::calculateDaysToWeeks($closestFutureAvailabilityDays + $this->getTransferDaysByDomainId($domainId));
    }

    /**
     * @param int $days
     * @return string
     */
    private function getWeeksAvailabilityMessageByDays(int $days): string
    {
        return $this->getWeeksAvailabilityMessageByWeeks(self::calculateDaysToWeeks($days));
    }

    /**
     * @param int $weeks
     * @return string
     */
    private function getWeeksAvailabilityMessageByWeeks(int $weeks): string
    {
        return tc(
            '{0,1} K dispozici za týden|[2,4] K dispozici za %weeks% týdny|[5,Inf] K dispozici za %weeks% týdnů',
            $weeks,
            ['%weeks%' => $weeks]
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
    private function getDeliveryDaysByDomainId(Product $product, int $domainId): int
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
     * @param int $domainId
     * @return int
     */
    private function getFutureStorageReservationByDomainId(int $domainId): int
    {
        return $this->setting->getForDomain(Setting::FUTURE_STORAGE_RESERVATION, $domainId);
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
        } else {
            return $this->getDeliveryDaysByDomainId($product, $domainId);
        }
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
    public function getGroupedStockQuantityWithFutureByProductAndDomainId(Product $product, int $domainId): int
    {
        $productStocksByDomainIdIndexedByStockId = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId($product, $domainId);
        $totalProductStocksQuantity = 0;
        foreach ($productStocksByDomainIdIndexedByStockId as $productStock) {
            $totalProductStocksQuantity += $productStock->getProductQuantity();
            if ($productStock->getDateOfStorage() !== null) {
                $totalProductStocksQuantity += $productStock->getFutureProductQuantity();
            }
        }

        return $totalProductStocksQuantity;
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

        return $this->getGroupedStockQuantityWithFutureByProductAndDomainId($product, $domainId);
    }

    /**
     * @param int $domainId
     * @param \App\Model\Stock\Stock[] $stocks
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return int[]
     */
    public function getStockDayAvailabilitiesIndexedByStockId(int $domainId, array $stocks, array $quantifiedProducts)
    {
        $maximumDayAvailabilityByStockId = [];
        foreach ($stocks as $stock) {
            $maximumDayAvailabilityByStockId[$stock->getId()] = 0;
        }

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $maximumDayAvailabilityByStockId = $this->getMaximumDayAvailabilityForProductIndexedByStockId(
                $quantifiedProduct,
                $maximumDayAvailabilityByStockId,
                $domainId
            );
        }

        return $maximumDayAvailabilityByStockId;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Stock\Stock[] $stocks
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Transport\Transport[] $transports
     * @return int[]
     */
    public function getMinimalDaysAvailabilityIndexedByTransportIds(int $domainId, array $stocks, array $quantifiedProducts, array $transports): array
    {
        $stockDayAvailabilities = $this->getStockDayAvailabilitiesIndexedByStockId($domainId, $stocks, $quantifiedProducts);
        asort($stockDayAvailabilities);

        $minimalStockDaysAvailability = reset($stockDayAvailabilities);

        $minimalDaysAvailabilityIndexedByTransportIds = [];
        foreach ($transports as $transport) {
            $minimalDaysAvailabilityIndexedByTransportIds[$transport->getId()] = $minimalStockDaysAvailability + $transport->getDaysUntilDelivery();
        }

        return $minimalDaysAvailabilityIndexedByTransportIds;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int[] $maximumDayAvailabilityByStockId
     * @param int $domainId
     * @return int[]
     */
    private function getMaximumDayAvailabilityForProductIndexedByStockId(
        QuantifiedProduct $quantifiedProduct,
        array $maximumDayAvailabilityByStockId,
        int $domainId
    ): array {
        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();
        $productStocksByDomainIdIndexedByStockId = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId(
            $product,
            $domainId
        );
        $quantityOnAllStocks = $this->sumProductStockQuantities($productStocksByDomainIdIndexedByStockId);

        foreach ($maximumDayAvailabilityByStockId as $stockId => $maximumDayAvailability) {
            $productDayAvailability = $this->getDayAvailabilityForProductAndStock(
                $quantifiedProduct,
                $productStocksByDomainIdIndexedByStockId[$stockId] ?? null,
                $quantityOnAllStocks,
                $domainId,
                $productStocksByDomainIdIndexedByStockId
            );

            $maximumDayAvailabilityByStockId[$stockId] = max(
                $maximumDayAvailability,
                $productDayAvailability
            );
        }

        return $maximumDayAvailabilityByStockId;
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
     * @param \App\Model\Stock\ProductStock[] $productStocks
     * @return int
     */
    private function getDayAvailabilityForProductAndStock(
        QuantifiedProduct $quantifiedProduct,
        ?ProductStock $productStock,
        int $quantityOnAllStocks,
        int $domainId,
        array $productStocks
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

        //the product will be on the stock($productStock) in future
        $requiredProductQuantity = $quantifiedProduct->getQuantity() - $quantityOnAllStocks;
        $closestFutureAvailabilityDays = $this->getClosestFutureAvailabilityDaysByProductStocksAndRequiredProductQuantity(
            $productStocks,
            $requiredProductQuantity,
            $productStock,
            $domainId
        );

        //we choose whether it is faster to transfer the product from other stocks
        $minimalAvailabilityDays = min($closestFutureAvailabilityDays, $productBetweenStockTransferDays);
        if ($minimalAvailabilityDays < PHP_INT_MAX) {
            return $minimalAvailabilityDays;
        }

        return $this->getDeliveryDaysByDomainId($product, $domainId);
    }

    /**
     * @param array $productStocks
     * @param int $requiredProductQuantity
     * @param \App\Model\Stock\ProductStock $productStock
     * @param int $domainId
     * @return int
     */
    private function getClosestFutureAvailabilityDaysByProductStocksAndRequiredProductQuantity(
        array $productStocks,
        int $requiredProductQuantity,
        ProductStock $productStock,
        int $domainId
    ): int {
        $closestFutureProductStock = $this->getClosestFutureProductStockByDomainId($productStocks, $requiredProductQuantity);
        $closestFutureAvailabilityDays = PHP_INT_MAX;
        if ($closestFutureProductStock !== null) {
            $closestFutureAvailabilityDays = $this->getFutureStockAvailabilityDaysByDomainId($closestFutureProductStock, $domainId);
            if ($closestFutureProductStock !== $productStock) {
                $closestFutureAvailabilityDays += $this->getTransferDaysByDomainId($domainId);
            }
        }

        return $closestFutureAvailabilityDays;
    }
}
