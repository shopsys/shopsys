<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPromoCodeFiller
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        protected readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        protected readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        protected readonly PromoCodeFlagRepository $promoCodeFlagRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodePerProductByDomainId(
        array $quantifiedProducts,
        int $domainId,
        PromoCode $promoCode,
    ): array {
        $allowedProductIds = $this->promoCodeProductRepository->getProductIdsByPromoCodeId($promoCode->getId());
        $allowedProductIdsByCriteria = $this->getAllowedProductIdsForBrandsAndCategories($promoCode, $domainId);

        $totalAllowedProductIds = array_unique(array_merge($allowedProductIds, $allowedProductIdsByCriteria));

        if (count($totalAllowedProductIds) === 0) {
            return $this->fillPromoCodeDiscountsForAllProducts($quantifiedProducts, $promoCode);
        }

        return $this->fillPromoCodes($quantifiedProducts, $totalAllowedProductIds, $promoCode);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    protected function fillPromoCodeDiscountsForAllProducts(
        array $quantifiedProducts,
        PromoCode $validEnteredPromoCode,
    ): array {
        $promoCodePercentPerProduct = [];

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $product = $quantifiedProduct->getProduct();
            $allowedProduct = $this->filterProductByPromoCodeFlags($product, $validEnteredPromoCode);

            if ($allowedProduct === null) {
                continue;
            }
            $productId = $allowedProduct->getId();
            $promoCodePercentPerProduct[(string)$productId] = $validEnteredPromoCode;
        }

        return $promoCodePercentPerProduct;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int[] $allowedProductIds
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    protected function fillPromoCodes(
        array $quantifiedProducts,
        array $allowedProductIds,
        PromoCode $validEnteredPromoCode,
    ): array {
        $promoCodeDiscountPercentPerProduct = [];

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $product = $quantifiedProduct->getProduct();
            $allowedProduct = $this->filterProductByPromoCodeFlags($product, $validEnteredPromoCode);

            if ($allowedProduct === null) {
                continue;
            }
            $productId = $allowedProduct->getId();

            if (in_array($productId, $allowedProductIds, true)) {
                $promoCodeDiscountPercentPerProduct[(string)$productId] = $validEnteredPromoCode;
            }
        }

        return $promoCodeDiscountPercentPerProduct;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function filterProductByPromoCodeFlags(Product $product, PromoCode $validEnteredPromoCode): ?Product
    {
        $promoCodeFlags = $this->promoCodeFlagRepository->getFlagsByPromoCodeId($validEnteredPromoCode->getId());

        $productFlagIds = $product->getFlagsIdsForDomain($this->domain->getId());
        $productSatisfies = true;

        foreach ($promoCodeFlags as $promoCodeFlag) {
            $productHasPromoCodeFlag = in_array($promoCodeFlag->getFlag()->getId(), $productFlagIds, true);

            if ($promoCodeFlag->isInclusive() && !$productHasPromoCodeFlag) {
                $productSatisfies = false;
            }

            if ($promoCodeFlag->isExclusive() && $productHasPromoCodeFlag) {
                $productSatisfies = false;
            }
        }

        return $productSatisfies ? $product : null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param int $domainId
     * @return int[]
     */
    public function getAllowedProductIdsForBrandsAndCategories(PromoCode $promoCode, int $domainId): array
    {
        $allowedProductIdsFromCategories = $this->promoCodeCategoryRepository->getProductIdsFromCategoriesByPromoCodeIdAndDomainId(
            $promoCode->getId(),
            $domainId,
        );
        $allowedProductIdsFromBrands = $this->promoCodeBrandRepository->getProductIdsFromBrandsByPromoCodeId(
            $promoCode->getId(),
        );

        if (count($allowedProductIdsFromCategories) !== 0 && count($allowedProductIdsFromBrands) !== 0) {
            return array_intersect($allowedProductIdsFromCategories, $allowedProductIdsFromBrands);
        }

        return array_merge($allowedProductIdsFromCategories, $allowedProductIdsFromBrands);
    }
}
