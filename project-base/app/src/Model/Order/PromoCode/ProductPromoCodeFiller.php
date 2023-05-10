<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductPromoCodeFiller
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductRepository
     */
    private PromoCodeProductRepository $promoCodeProductRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryRepository
     */
    private PromoCodeCategoryRepository $promoCodeCategoryRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeBrandRepository
     */
    private PromoCodeBrandRepository $promoCodeBrandRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository
     */
    private PromoCodeFlagRepository $promoCodeFlagRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \App\Model\Order\PromoCode\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     */
    public function __construct(
        Domain $domain,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository,
        PromoCodeBrandRepository $promoCodeBrandRepository,
        PromoCodeFlagRepository $promoCodeFlagRepository
    ) {
        $this->domain = $domain;
        $this->promoCodeProductRepository = $promoCodeProductRepository;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
        $this->promoCodeBrandRepository = $promoCodeBrandRepository;
        $this->promoCodeFlagRepository = $promoCodeFlagRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @return \App\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodePerProductByDomainId(array $quantifiedProducts, int $domainId, PromoCode $promoCode): array
    {
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
     * @param \App\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return \App\Model\Order\PromoCode\PromoCode[]
     */
    private function fillPromoCodeDiscountsForAllProducts(array $quantifiedProducts, PromoCode $validEnteredPromoCode): array
    {
        $promoCodePercentPerProduct = [];
        foreach ($quantifiedProducts as $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
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
     * @param \App\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return \App\Model\Order\PromoCode\PromoCode[]
     */
    private function fillPromoCodes(array $quantifiedProducts, array $allowedProductIds, PromoCode $validEnteredPromoCode): array
    {
        $promoCodeDiscountPercentPerProduct = [];
        foreach ($quantifiedProducts as $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
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
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return \App\Model\Product\Product|null
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param int $domainId
     * @return int[]
     */
    public function getAllowedProductIdsForBrandsAndCategories(PromoCode $promoCode, int $domainId): array
    {
        $allowedProductIdsFromCategories = $this->promoCodeCategoryRepository->getProductIdsFromCategoriesByPromoCodeIdAndDomainId(
            $promoCode->getId(),
            $domainId
        );
        $allowedProductIdsFromBrands = $this->promoCodeBrandRepository->getProductIdsFromBrandsByPromoCodeId(
            $promoCode->getId()
        );

        if (count($allowedProductIdsFromCategories) !== 0 && count($allowedProductIdsFromBrands) !== 0) {
            return array_intersect($allowedProductIdsFromCategories, $allowedProductIdsFromBrands);
        }

        return array_merge($allowedProductIdsFromCategories, $allowedProductIdsFromBrands);
    }
}
