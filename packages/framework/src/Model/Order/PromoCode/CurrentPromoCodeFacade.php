<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\AvailableForRegisteredCustomerUserOnly;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\LimitNotReachedException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NotAvailableForCustomerUserPricingGroup;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;

class CurrentPromoCodeFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     */
    public function __construct(
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        protected readonly Domain $domain,
        protected readonly ProductPromoCodeFiller $productPromoCodeFiller,
        protected readonly PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function validatePromoCodeDatetime(PromoCode $promoCode): void
    {
        if ($promoCode->getDatetimeValidFrom() === null
            && $promoCode->getDatetimeValidTo() === null
        ) {
            return;
        }

        $currentTimestamp = time();

        if ($promoCode->getDatetimeValidFrom() !== null
            && $promoCode->getDatetimeValidTo() !== null
        ) {
            if ($promoCode->getDatetimeValidFrom()->getTimestamp() < $currentTimestamp
                && $promoCode->getDatetimeValidTo()->getTimestamp() > $currentTimestamp
            ) {
                return;
            }
        }

        if ($promoCode->getDatetimeValidFrom() !== null && $promoCode->getDatetimeValidFrom()->getTimestamp() > $currentTimestamp) {
            throw new NotYetValidPromoCodeDateTimeException($promoCode->getCode());
        }

        if ($promoCode->getDatetimeValidTo() !== null && $promoCode->getDatetimeValidTo()->getTimestamp() < $currentTimestamp) {
            throw new NoLongerValidPromoCodeDateTimeException($promoCode->getCode());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function validateRemainingUses(PromoCode $promoCode): void
    {
        $remainingCodeUses = $promoCode->getRemainingUses();

        if ($remainingCodeUses !== null && $remainingCodeUses === 0) {
            throw new InvalidPromoCodeException($promoCode->getCode());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function validatePromoCodeByProductsInCart(PromoCode $promoCode, Cart $cart)
    {
        $domainId = $this->domain->getId();
        $allowedProductIds = $this->promoCodeProductRepository->getProductIdsByPromoCodeId($promoCode->getId());
        $allowedProductIdsByCriteria = $this->productPromoCodeFiller->getAllowedProductIdsForBrandsAndCategories($promoCode, $domainId);

        $allowedProductIds = array_unique(array_merge($allowedProductIds, $allowedProductIdsByCriteria));

        if (count($allowedProductIds) === 0) {
            //promo code hasn't any relation with products or product from categories or product from brands
            return;
        }

        $isValidPromoCode = false;

        foreach ($cart->getItems() as $cartItem) {
            if (in_array($cartItem->getProduct()->getId(), $allowedProductIds, true) === true) {
                $isValidPromoCode = true;

                break;
            }
        }

        if ($isValidPromoCode === false) {
            throw new PromoCodeWithoutRelationWithAnyProductFromCurrentCartException($promoCode);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function validateLimit(PromoCode $promoCode, Cart $cart): void
    {
        $limit = $this->promoCodeLimitByCartTotalResolver->getLimitByPromoCode(
            $promoCode,
            $cart->getQuantifiedProducts(),
        );

        if ($limit === null) {
            throw new LimitNotReachedException($promoCode);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function validatePromoCodeByFlags(PromoCode $promoCode, Cart $cart): void
    {
        $isValidPromoCode = false;

        foreach ($cart->getItems() as $cartItem) {
            $productFromCart = $cartItem->getProduct();
            $product = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productFromCart, $promoCode);

            if ($product !== null) {
                $isValidPromoCode = true;

                break;
            }
        }

        if ($isValidPromoCode === false) {
            throw new PromoCodeWithoutRelationWithAnyProductFromCurrentCartException($promoCode);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodePerProductByDomainId(
        array $quantifiedProducts,
        int $domainId,
        ?PromoCode $promoCode = null,
    ): array {
        if ($promoCode === null) {
            return [];
        }

        return $this->productPromoCodeFiller->getPromoCodePerProductByDomainId($quantifiedProducts, $domainId, $promoCode);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function validatePricingGroup(PromoCode $promoCode): void
    {
        $limitedPricingGroups = $this->promoCodePricingGroupRepository->getPricingGroupsByPromoCodeId(
            $promoCode->getId(),
        );

        if (count($limitedPricingGroups) === 0) {
            return;
        }

        foreach ($limitedPricingGroups as $pricingGroup) {
            if ($pricingGroup->getId() === $this->currentCustomerUser->getPricingGroup()->getId()) {
                return;
            }
        }

        throw new NotAvailableForCustomerUserPricingGroup($promoCode->getCode(), $this->currentCustomerUser->getPricingGroup()->getId());
    }

    /**
     * @param string $enteredCode
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function getValidatedPromoCode(string $enteredCode, Cart $cart): PromoCode
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($enteredCode, $this->domain->getId());

        if ($promoCode === null) {
            throw new InvalidPromoCodeException($enteredCode);
        }

        if ($promoCode->isRegisteredCustomerUserOnly() && $this->currentCustomerUser->findCurrentCustomerUser() === null) {
            throw new AvailableForRegisteredCustomerUserOnly($enteredCode);
        }

        $this->validatePricingGroup($promoCode);
        $this->validatePromoCodeDatetime($promoCode);
        $this->validateRemainingUses($promoCode);
        $this->validatePromoCodeByProductsInCart($promoCode, $cart);
        $this->validatePromoCodeByFlags($promoCode, $cart);
        $this->validateLimit($promoCode, $cart);

        return $promoCode;
    }
}
