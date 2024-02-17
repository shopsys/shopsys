<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductPriceExportScope extends AbstractProductExportScope
{

    public function __construct(
        private readonly ProductFacade $productFacade,
    )
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'prices' => $this->extractPrices($domainId, $object),
        ];
    }

    public function getDependencies(): array
    {
        return [
            ProductVisibilityExportScope::class,
        ];
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractPrices(int $domainId, Product $product): array
    {
        $prices = [];
        $productSellingPrices = $this->productFacade->getAllProductSellingPricesByDomainId($product, $domainId);

        foreach ($productSellingPrices as $productSellingPrice) {
            $sellingPrice = $productSellingPrice->getSellingPrice();
            $priceFrom = false;

            if ($sellingPrice instanceof ProductPrice) {
                $priceFrom = $sellingPrice->isPriceFrom();
            }

            $prices[] = [
                'pricing_group_id' => $productSellingPrice->getPricingGroup()->getId(),
                'price_with_vat' => (float)$sellingPrice->getPriceWithVat()->getAmount(),
                'price_without_vat' => (float)$sellingPrice->getPriceWithoutVat()->getAmount(),
                'vat' => (float)$sellingPrice->getVatAmount()->getAmount(),
                'price_from' => $priceFrom,
            ];
        }

        return $prices;
    }
}