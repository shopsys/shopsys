<?php

declare(strict_types=1);


namespace App\Model\Product\Availability;


use App\Component\Setting\Setting;
use App\Model\Product\Product;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\ProductStockRepository;

class ProductAvailabilityFacade
{
    private const IN_STOCK = 'Skladem';
    private const AVAILABLE_AFTER_WEEKS = 'K dispozici za %weeks% týdnů';
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

    public function __construct(
        ProductStockRepository $productStockRepository,
        Setting $setting,
        ProductStockFacade $productStockFacade
    )
    {
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
        if($this->isProductAvailableOnDomain($product, $domainId)){
            return t(self::IN_STOCK);
        }

        return t(self::AVAILABLE_AFTER_WEEKS,['%weeks%' => $this->getDeliveryWeeksByDomainId($domainId)]);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        $productStocks = $this->productStockFacade->getProductStocksByProduct($product);

        foreach ($productStocks as $productStock){
            if($productStock->getStock()->getDomainId() === $domainId && $productStock->getProductQuantity() > 0){
                return true;
            }
        }
        return false;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return ProductStockAvailabilityInformation[]
     */
    public function getProductStocksAvailabilitiesInformationByDomainId(Product $product, int $domainId): array
    {
        $productStocks = $this->productStockRepository->getProductStocksExcludeCentralStockByProductAndDomainId($product, $domainId);

        if($this->isProductAvailableOnDomain($product, $domainId)){
            $outOfStockAvailabilityInformation = t(self::AVAILABLE_AFTER_WEEKS,['%weeks%' => $this->getTransferWeeksByDomainId($domainId)]);
        }else{
            $outOfStockAvailabilityInformation = t(self::AVAILABLE_AFTER_WEEKS,['%weeks%' => $this->getDeliveryWeeksByDomainId($domainId)]);
        }

        $stocksList = [];
        foreach ($productStocks as $productStock){
            $availabilityInformation = self::IN_STOCK;

            if($productStock->getProductQuantity() <= 0){
                $availabilityInformation = $outOfStockAvailabilityInformation;
            }

            $stocksList[] = new ProductStockAvailabilityInformation(
                $productStock->getStock()->getName(), $availabilityInformation
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