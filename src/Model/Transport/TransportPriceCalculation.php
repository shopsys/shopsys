<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Product\Package\ProductPackageRepository;
use App\Model\Transport\Logistic\TransportLogisticFacade;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation as BaseTransportPriceCalculation;

/**
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculatePrice(\App\Model\Transport\Transport $transport, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculateIndependentPrice(\App\Model\Transport\Transport $transport, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getCalculatedPricesIndexedByTransportId(\App\Model\Transport\Transport[] $transports, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency, \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice, int $domainId)
 */
class TransportPriceCalculation extends BaseTransportPriceCalculation
{
    /**
     * @var \App\Model\Product\Package\ProductPackageRepository
     */
    private ProductPackageRepository $productPackageRepository;

    /**
     * @var \App\Model\Transport\Logistic\TransportLogisticFacade
     */
    private TransportLogisticFacade $transportLogisticFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \App\Model\Product\Package\ProductPackageRepository $productPackageRepository
     * @param \App\Model\Transport\Logistic\TransportLogisticFacade $transportLogisticFacade
     */
    public function __construct(
        BasePriceCalculation $basePriceCalculation,
        PricingSetting $pricingSetting,
        ProductPackageRepository $productPackageRepository,
        TransportLogisticFacade $transportLogisticFacade
    ) {
        parent::__construct($basePriceCalculation, $pricingSetting);

        $this->productPackageRepository = $productPackageRepository;
        $this->transportLogisticFacade = $transportLogisticFacade;
    }

    /**
     * @param \App\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param bool $transportForFree
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getCalculatedPricesIndexedByTransportIdByFreeTransport(
        array $transports,
        Currency $currency,
        int $domainId,
        array $quantifiedProducts,
        bool $transportForFree
    ): array {
        $transportsPricesByTransportId = [];
        foreach ($transports as $transport) {
            if ($transportForFree) {
                $transportsPricesByTransportId[$transport->getId()] = Price::zero();
                continue;
            }

            $transportsPricesByTransportId[$transport->getId()] = $this->calculatePriceByQuantifiedProducts(
                $transport,
                $currency,
                $domainId,
                $quantifiedProducts
            );
        }

        return $transportsPricesByTransportId;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function calculatePriceByQuantifiedProducts(
        Transport $transport,
        Currency $currency,
        int $domainId,
        array $quantifiedProducts
    ): Price {
        if ($transport->getType() !== Transport::TYPE_PACKAGE) {
            return $this->calculateIndependentPrice($transport, $currency, $domainId);
        }

        $productPackagesCollection = $this->productPackageRepository->getProductPackagesCollectionByQuantifiedProducts($quantifiedProducts);
        $transportPackage = $this->transportLogisticFacade->findFirstPossibleTransportPackageByProductPackageCollection($transport, $productPackagesCollection, $domainId);

        if ($transportPackage === null) {
            $message = sprintf('Possible transport package not found and price can not be calculated (Transport::$id=%d)', $transport->getId());
            throw new App\Model\Transport\TransportPackage\Exception\TransportPackageNotFoundException($message);
        }

        return $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $transportPackage->getPriceWithVat(),
            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
            $transport->getTransportDomain($domainId)->getVat(),
            $currency
        );
    }
}
