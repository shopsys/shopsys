<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Symfony\Component\Clock\DatePoint;

class SpecialPriceApiFactory
{
    public function __construct(
        protected readonly SpecialPriceFactory $specialPriceFactory,
        protected readonly PriceFactory $priceFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function createSpecialPriceFromArray(array $data, PriceInterface $basicPrice): ?SpecialPrice
    {
        $specialPricesArray = $data['special_prices'];

        if (count($specialPricesArray) === 0) {
            return null;
        }

        $specialPrice = $this->findRelevantSpecialPrice($specialPricesArray);

        if ($specialPrice !== null && $specialPrice->price->getPriceWithVat()->isGreaterThanOrEqualTo($basicPrice->getPriceWithVat())) {
            return null;
        }

        return $specialPrice;
    }

    protected function findRelevantSpecialPrice(array $specialPricesArray): ?SpecialPrice
    {
        $currentDateTime = $this->clock->now();
        $usedProductIds = [];
        $finalSpecialPrice = null;

        foreach ($specialPricesArray as $specialPriceArray) {
            foreach ($specialPriceArray['prices'] as $priceArray) {
                $specialPriceCandidate = $this->createSpecialPrice($specialPriceArray, $priceArray);

                if ($currentDateTime >= $specialPriceCandidate->validFrom && $currentDateTime <= $specialPriceCandidate->validTo) {
                    if (array_key_exists($specialPriceCandidate->productId, $usedProductIds)) {
                        continue;
                    }

                    if ($finalSpecialPrice === null || $finalSpecialPrice->price->getPriceWithVat()->isGreaterThan($specialPriceCandidate->price->getPriceWithVat())) {
                        $finalSpecialPrice = $specialPriceCandidate;
                    }

                    $usedProductIds[$specialPriceCandidate->productId] = true;
                }

                if ($finalSpecialPrice === null && $currentDateTime < $specialPriceCandidate->validFrom) {
                    return $specialPriceCandidate;
                }
            }
        }

        return $finalSpecialPrice;
    }

    /**
     * @param array{valid_from: string, valid_to: string, price_list_id: int, price_list_name: string} $specialPriceArray
     * @param array{price_without_vat: string, price_with_vat: string, product_id: int} $priceArray
     */
    protected function createSpecialPrice(array $specialPriceArray, array $priceArray): SpecialPrice
    {
        return $this->specialPriceFactory->create(
            $this->priceFactory->createPriceFromArray($priceArray),
            new DatePoint($specialPriceArray['valid_from']),
            new DatePoint($specialPriceArray['valid_to']),
            $specialPriceArray['price_list_id'],
            $specialPriceArray['price_list_name'],
            $priceArray['product_id'],
        );
    }
}
