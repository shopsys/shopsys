<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPromoCodeFiller
{
    protected const string PROMO_CODE_FLAGS_CACHE_NAMESPACE = 'promoCodeFlagsByPromoCodeId';

    protected const string ALLOWED_PRODUCT_IDS_CACHE_NAMESPACE = 'promoCodeAllowedProductIdsByPromoCodeId';

    protected const string ALLOWED_PRODUCT_IDS_BY_CRITERIA_CACHE_NAMESPACE = 'promoCodeAllowedProductIdsByBrandsAndCategories';

    public function __construct(
        protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        protected readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        protected readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        protected readonly PromoCodeFlagRepository $promoCodeFlagRepository,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return array<int, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode>
     */
    public function getPromoCodePerProductByDomainId(
        array $quantifiedProducts,
        int $domainId,
        PromoCode $promoCode,
    ): array {
        $allowedProductIds = $this->getAllowedProductIds($promoCode);
        $allowedProductIdsByCriteria = $this->getAllowedProductIdsForBrandsAndCategories($promoCode, $domainId);

        $totalAllowedProductIds = array_unique(array_merge($allowedProductIds, $allowedProductIdsByCriteria));

        if (count($totalAllowedProductIds) === 0) {
            return $this->fillPromoCodeDiscountsForAllProducts($quantifiedProducts, $promoCode, $domainId);
        }

        return $this->fillPromoCodes(
            $quantifiedProducts,
            $totalAllowedProductIds,
            $promoCode,
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    protected function fillPromoCodeDiscountsForAllProducts(
        array $quantifiedProducts,
        PromoCode $validEnteredPromoCode,
        int $domainId,
    ): array {
        $promoCodePercentPerProduct = [];

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $product = $quantifiedProduct->getProduct();
            $allowedProduct = $this->filterProductByPromoCodeFlags($product, $validEnteredPromoCode, $domainId);

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
     * @return array<int, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode>
     */
    protected function fillPromoCodes(
        array $quantifiedProducts,
        array $allowedProductIds,
        PromoCode $validEnteredPromoCode,
        int $domainId,
    ): array {
        $promoCodeDiscountPercentPerProduct = [];

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $product = $quantifiedProduct->getProduct();
            $allowedProduct = $this->filterProductByPromoCodeFlags($product, $validEnteredPromoCode, $domainId);

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

    public function filterProductByPromoCodeFlags(
        Product $product,
        PromoCode $validEnteredPromoCode,
        int $domainId,
    ): ?Product {
        $promoCodeFlags = $this->getPromoCodeFlags($validEnteredPromoCode);

        $productFlagIds = $product->getFlagsIdsForDomain($domainId);
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
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[]
     */
    protected function getPromoCodeFlags(PromoCode $promoCode): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PROMO_CODE_FLAGS_CACHE_NAMESPACE,
            fn (): array => $this->promoCodeFlagRepository->getFlagsByPromoCodeId($promoCode->getId()),
            $promoCode->getId(),
        );
    }

    /**
     * @return int[]
     */
    public function getAllowedProductIds(PromoCode $promoCode): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::ALLOWED_PRODUCT_IDS_CACHE_NAMESPACE,
            fn (): array => $this->promoCodeProductRepository->getProductIdsByPromoCodeId($promoCode->getId()),
            $promoCode->getId(),
        );
    }

    /**
     * @return int[]
     */
    public function getAllowedProductIdsForBrandsAndCategories(PromoCode $promoCode, int $domainId): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::ALLOWED_PRODUCT_IDS_BY_CRITERIA_CACHE_NAMESPACE,
            fn (): array => $this->loadAllowedProductIdsForBrandsAndCategories($promoCode, $domainId),
            $promoCode->getId(),
            $domainId,
        );
    }

    /**
     * @return int[]
     */
    protected function loadAllowedProductIdsForBrandsAndCategories(PromoCode $promoCode, int $domainId): array
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
