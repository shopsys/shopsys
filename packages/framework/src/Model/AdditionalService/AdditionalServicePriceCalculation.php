<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Model\AdditionalService\Exception\AdditionalServiceVatNotSetException;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Product;

class AdditionalServicePriceCalculation
{
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PriceCalculation $priceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    public function calculatePrice(
        AdditionalService $additionalService,
        Product $product,
        int $domainId,
    ): PriceInterface {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $additionalService->getPriceForDomain($domainId),
            $this->pricingSetting->getInputPriceType(),
            $this->getVat($additionalService, $product, $domainId),
            $currency->getRoundingType(),
            $currency->getRoundingPlacesPriceWithoutVat(),
        );
    }

    public function calculateTotalPrice(
        AdditionalService $additionalService,
        Product $product,
        int $domainId,
        int $quantity,
    ): PriceInterface {
        $unitPrice = $this->calculatePrice($additionalService, $product, $domainId);
        $totalPriceWithVat = $unitPrice->getPriceWithVat()->multiply($quantity);

        switch ($this->pricingSetting->getInputPriceType()) {
            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $totalPriceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat(
                    $totalPriceWithVat,
                    $this->getVat($additionalService, $product, $domainId),
                    $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId)->getRoundingPlacesPriceWithoutVat(),
                );
                $totalPriceWithoutVat = $totalPriceWithVat->subtract($totalPriceVatAmount);

                break;
            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $totalPriceWithoutVat = $unitPrice->getPriceWithoutVat()->multiply($quantity);

                break;
            default:
                throw new InvalidInputPriceTypeException();
        }

        return new Price($totalPriceWithoutVat, $totalPriceWithVat);
    }

    public function getVat(AdditionalService $additionalService, Product $product, int $domainId): Vat
    {
        if ($additionalService->isProductVatRateUsed($domainId)) {
            return $product->getVatForDomain($domainId);
        }

        $vat = $additionalService->getVatForDomain($domainId);

        if ($vat === null) {
            throw new AdditionalServiceVatNotSetException($additionalService->getId(), $domainId);
        }

        return $vat;
    }
}
