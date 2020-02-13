<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use App\Component\Setting\Setting;
use App\Model\Product\Product;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\ProductStockRepository;

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
        $weeks = $this->getDeliveryWeeksByDomainId($domainId);

        return tc(
            '{1} K dispozici za týden|[2,4] K dispozici za %weeks% týdny|[5,Inf] K dispozici za %weeks% týdnů',
            $weeks,
            ['%weeks%' => $weeks]
        );
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
     * @return \App\Model\Product\Availability\ProductStockAvailabilityInformation[]
     */
    public function getProductStocksAvailabilitiesInformationByDomainId(Product $product, int $domainId): array
    {
        $productStocks = $this->productStockRepository->getProductStocksExcludeCentralStockByProductAndDomainId($product, $domainId);

        if ($this->isProductAvailableOnDomain($product, $domainId)) {
            $weeks = $this->getTransferWeeksByDomainId($domainId);
        } else {
            $weeks = $this->getDeliveryWeeksByDomainId($domainId);
        }
        $outOfStockAvailabilityInformation = tc(
            '{1} K dispozici za týden|[2,4] K dispozici za %weeks% týdny|[5,Inf] K dispozici za %weeks% týdnů',
            $weeks,
            ['%weeks%' => $weeks]
        );

        $stocksList = [];
        foreach ($productStocks as $productStock) {
            $availabilityInformation = t('Skladem');

            if ($productStock->getProductQuantity() <= 0) {
                $availabilityInformation = $outOfStockAvailabilityInformation;
            }

            $stocksList[] = new ProductStockAvailabilityInformation(
                $productStock->getStock()->getName(),
                $availabilityInformation
            );
        }

        return $stocksList;
    }

    /**
     * @param int $domainId
     * @return int
     */
    private function getDeliveryWeeksByDomainId(int $domainId): int
    {
        $deliveryDays = $this->setting->getForDomain(Setting::DELIVERY_DAYS_ON_STOCK, $domainId);

        return (int)ceil($deliveryDays / self::DAYS_IN_WEEK);
    }

    /**
     * @param int $domainId
     * @return int
     */
    private function getTransferWeeksByDomainId(int $domainId): int
    {
        $transferDays = $this->setting->getForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, $domainId);

        return (int)ceil($transferDays / self::DAYS_IN_WEEK);
    }
}
