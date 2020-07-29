<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\Domain\Domain;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;

class ProductPromoCodeFiller
{
    private const BIT_ON_SALE = 1;
    private const BIT_IN_ACTION = 2;
    private const BIT_SCONTO_PRICE = 4;
    private const BIT_WITHOUT_LOW_PRICE = 8;

    /**
     * @var \App\Component\Domain\Domain
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
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     */
    public function __construct(
        Domain $domain,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository
    ) {
        $this->domain = $domain;
        $this->promoCodeProductRepository = $promoCodeProductRepository;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
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
        $allowedProductIdsFromCategories = $this->promoCodeCategoryRepository->getProductIdsFromCategoriesByPromoCodeIdAndDomainId($promoCode->getId(), $domainId);

        if (count(array_unique(array_merge($allowedProductIds, $allowedProductIdsFromCategories))) === 0) {
            return $this->fillPromoCodeDiscountsForAllProducts($quantifiedProducts, $promoCode);
        }

        $promoCodeDiscountPercentPerProduct = $this->fillPromoCodes($quantifiedProducts, $allowedProductIds, $promoCode);
        $promoCodeDiscountPercentPerProductFromCategories = $this->fillPromoCodes($quantifiedProducts, $allowedProductIdsFromCategories, $promoCode);

        return array_replace($promoCodeDiscountPercentPerProduct, $promoCodeDiscountPercentPerProductFromCategories);
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
            if ($validEnteredPromoCode->isApplyOnSecondProduct() && $quantifiedProduct->getQuantity() < 2) {
                continue;
            }
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
        $filterMask = 0;
        $filterMask += $validEnteredPromoCode->isOnSale() ? self::BIT_ON_SALE : 0;
        $filterMask += $validEnteredPromoCode->isInAction() ? self::BIT_IN_ACTION : 0;
        $filterMask += $validEnteredPromoCode->isScontoPrice() ? self::BIT_SCONTO_PRICE : 0;
        $filterMask += $validEnteredPromoCode->isWithoutLowPrice() ? self::BIT_WITHOUT_LOW_PRICE : 0;

        $productSetup = 0;
        $productSetup += $this->hasProductFlagByAkeneoCode($product, Flag::AKENEO_CODE_SALE) ? self::BIT_ON_SALE : 0;
        $productSetup += $this->hasProductFlagByAkeneoCode($product, Flag::AKENEO_CODE_ACTION) ? self::BIT_IN_ACTION : 0;
        $productSetup += $this->hasProductFlagByAkeneoCode($product, Flag::AKENEO_CODE_SCONTO) ? self::BIT_SCONTO_PRICE : 0;
        $productSetup += $product->getLowPriceWithVat($this->domain->getId())->isZero() ? self::BIT_WITHOUT_LOW_PRICE : 0;

        if (($filterMask & $productSetup ^ $filterMask) === 0) {
            return $product;
        }

        return null;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $akeneoCode
     * @return bool
     */
    private function hasProductFlagByAkeneoCode(Product $product, string $akeneoCode): bool
    {
        foreach ($product->getFlagsForDomain($this->domain->getId()) as $flag) {
            if ($flag->getAkeneoCode() === $akeneoCode) {
                return true;
            }
        }

        return false;
    }
}
