<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;

class SpecialPriceApiFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFactory $specialPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory $priceFactory
     */
    public function __construct(
        protected readonly SpecialPriceFactory $specialPriceFactory,
        protected readonly PriceFactory $priceFactory,
    ) {
    }

    /**
     * @param array $data
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $basicPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice|null
     */
    public function createSpecialPriceFromArray(array $data, Price $basicPrice): ?SpecialPrice
    {
        $specialPricesArray = $data['special_prices'];

        if (count($specialPricesArray) === 0) {
            return null;
        }

        $effectiveSpecialPrice = $this->getEffectiveSpecialPrice($specialPricesArray);

        if ($effectiveSpecialPrice !== null && $effectiveSpecialPrice->price->getPriceWithVat()->isGreaterThanOrEqualTo($basicPrice->getPriceWithVat())) {
            return null;
        }

        return $effectiveSpecialPrice;
    }

    /**
     * @param array $specialPricesArray
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice|null
     */
    protected function getEffectiveSpecialPrice(array $specialPricesArray): ?SpecialPrice
    {
        $currentDateTime = new DateTimeImmutable();
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice
     */
    protected function createSpecialPrice(array $specialPriceArray, array $priceArray): SpecialPrice
    {
        return $this->specialPriceFactory->create(
            $this->priceFactory->createPriceFromArray($priceArray),
            new DateTimeImmutable($specialPriceArray['valid_from']),
            new DateTimeImmutable($specialPriceArray['valid_to']),
            $specialPriceArray['price_list_id'],
            $specialPriceArray['price_list_name'],
            $priceArray['product_id'],
        );
    }
}
