<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use App\Component\Setting\Setting;
use App\Model\Product\Product;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\ProductStockRepository;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

class ProductAvailabilityFacade
{
    private const DAYS_IN_WEEK = 7;

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
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityInformationByDomainId(Product $product, int $domainId): string
    {
        if ($this->isProductAvailableOnDomain($product, $domainId)) {
            return t('Skladem');
        }

        if ($product->hasPreorder() === false) {
            return t('Vyprodáno');
        }

        return $this->getAvailableForWeeksMessageByProductAndDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    private function getAvailableForWeeksMessageByProductAndDomainId(Product $product, int $domainId): string
    {
        $weeks = $this->getDeliveryWeeksByDomainId($domainId, $product);

        return tc(
            '{0,1} K dispozici za týden|[2,4] K dispozici za %weeks% týdny|[5,Inf] K dispozici za %weeks% týdnů',
            $weeks,
            ['%weeks%' => $weeks]
        );
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

        if ($this->getGroupedStockQuantity($product, $domainId) >= $quantifiedProduct->getQuantity()) {
            return t('Skladem');
        }

        if ($product->hasPreorder() === false) {
            return t('Vyprodáno');
        }

        return $this->getAvailableForWeeksMessageByProductAndDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    public function getProductAvailabilityStatusByDomainId(Product $product, int $domainId): string
    {
        $availabilityStatus = 'out-of-stock';

        if ($this->isProductAvailableOnDomain($product, $domainId)) {
            $availabilityStatus = 'in-stock';
        }

        return $availabilityStatus;
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
        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);

        $count = 0;
        foreach ($productStocks as $productStock) {
            if ($productStock->getStock()->getDomainId() === $domainId
                && $productStock->getStock()->isCentralStock() === false
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
        if ($this->isProductAvailableOnDomain($product, $domainId)) {
            return 0;
        }

        return $this->getDeliveryDaysByDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);

        foreach ($productStocks as $productStock) {
            if ($productStock->getStock()->getDomainId() === $domainId && $productStock->getProductQuantity() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductExcludedOnDomain(Product $product, int $domainId): bool
    {
        return $product->getSaleExclusion($domainId) && !$this->isProductAvailableOnDomain(
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
        return $product->hasPreorder() || $this->isProductAvailableOnDomain(
            $product,
            $domainId
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Availability\ProductStockAvailabilityInformation[]
     */
    public function getProductStocksAvailabilitiesInformationByDomainId(Product $product, int $domainId): array
    {
        $productStocks = $this->productStockRepository->getProductStocksExcludeCentralStockByProductAndDomainId($product, $domainId);

        if ($this->isProductAvailableOnDomain($product, $domainId)) {
            $weeks = $this->getTransferWeeksByDomainId($domainId);
        } else {
            $weeks = $this->getDeliveryWeeksByDomainId($domainId, $product);
        }

        $outOfStockAvailabilityInformation = tc(
            '{0,1} K dispozici za týden|[2,4] K dispozici za %weeks% týdny|[5,Inf] K dispozici za %weeks% týdnů',
            $weeks,
            ['%weeks%' => $weeks]
        );

        if ($product->hasPreorder() === false) {
            $outOfStockAvailabilityInformation = t('Vyprodáno');
        }

        $stocksList = [];
        foreach ($productStocks as $productStock) {
            $availabilityInformation = t('Můžete mít <strong class="is-in-stock">ihned</strong>');
            $availabilityStatus = 'in-stock';

            if ($productStock->getProductQuantity() <= 0) {
                $availabilityInformation = $outOfStockAvailabilityInformation;
                $availabilityStatus = 'out-of-stock';
            }

            $stocksList[] = new ProductStockAvailabilityInformation(
                $productStock->getStock()->getName(),
                $availabilityInformation,
                $productStock->isProductExposed(),
                $availabilityStatus
            );
        }

        return $stocksList;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Product\Product $product
     * @throws \Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException
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
    private function getTransferDaysByDomainId(int $domainId): int
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
        if ($this->isProductAvailableOnDomain($product, $domainId)) {
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
    public function getGroupedStockQuantity(Product $product, int $domainId): int
    {
        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);
        $groupedStockQuantity = 0;

        foreach ($productStocks as $productStock) {
            if ($productStock->getStock()->getDomainId() === $domainId) {
                $groupedStockQuantity += $productStock->getProductQuantity();
            }
        }

        return $groupedStockQuantity;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return int
     */
    public function getMaximumOrderQuantity(Product $product, int $domainId): int
    {
        return ($product->hasPreorder()) ? PHP_INT_MAX : $this->getGroupedStockQuantity($product, $domainId);
    }
}
