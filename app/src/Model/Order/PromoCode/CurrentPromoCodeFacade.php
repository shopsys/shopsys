<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Model\Cart\Cart;
use App\Model\Order\PromoCode\Exception\AvailableForRegisteredCustomerUserOnly;
use App\Model\Order\PromoCode\Exception\LimitNotReachedException;
use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotAvailableForCustomerUserPricingGroup;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade as BaseCurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 */
class CurrentPromoCodeFacade extends BaseCurrentPromoCodeFacade
{
    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller
     * @param \App\Model\Order\PromoCode\PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        RequestStack $requestStack,
        private readonly PromoCodeProductRepository $promoCodeProductRepository,
        private readonly Domain $domain,
        private readonly ProductPromoCodeFiller $productPromoCodeFiller,
        private readonly PromoCodeLimitResolver $promoCodeLimitByCartTotalResolver,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository
    ) {
        parent::__construct(
            $promoCodeFacade,
            $requestStack
        );
    }

    /**
     * @param string $enteredCode
     * @deprecated use App\Model\Cart\CartPromoCodeFacade::applyPromoCodeByCode() instead
     */
    public function setEnteredPromoCode($enteredCode): void
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated use App\Model\Cart\Cart::getFirstAppliedPromoCode
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function getValidEnteredPromoCodeOrNull(): ?PromoCode
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated use App\Model\Cart\CartPromoCodeFacade::removePromoCode() instead
     */
    public function removeEnteredPromoCode(): void
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    private function validatePromoCodeDatetime(PromoCode $promoCode): void
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function validateRemainigUses(PromoCode $promoCode): void
    {
        $remainingCodeUses = $promoCode->getRemainingUses();
        if ($remainingCodeUses !== null && $remainingCodeUses === 0) {
            throw new InvalidPromoCodeException($promoCode->getCode());
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Cart\Cart $cart
     */
    private function validatePromoCodeByProductsInCart(PromoCode $promoCode, Cart $cart)
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
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Cart\Cart $cart
     */
    private function validateLimit(PromoCode $promoCode, Cart $cart): void
    {
        $limit = $this->promoCodeLimitByCartTotalResolver->getLimitByPromoCode(
            $promoCode,
            $cart->getQuantifiedProducts()
        );
        if ($limit === null) {
            throw new LimitNotReachedException($promoCode);
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Cart\Cart $cart
     */
    private function validatePromoCodeByFlags(PromoCode $promoCode, Cart $cart)
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
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return \App\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodePerProductByDomainId(array $quantifiedProducts, int $domainId, ?PromoCode $promoCode = null): array
    {
        if ($promoCode === null) {
            return [];
        }

        return $this->productPromoCodeFiller->getPromoCodePerProductByDomainId($quantifiedProducts, $domainId, $promoCode);
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    private function validatePricingGroup(PromoCode $promoCode): void
    {
        $limitedPricingGroups = $this->promoCodePricingGroupRepository->getPricingGroupsByPromoCodeId(
            $promoCode->getId()
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
     * @param \App\Model\Cart\Cart $cart
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    public function getValidatedPromoCode(string $enteredCode, Cart $cart): PromoCode
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($promoCode === null) {
            throw new InvalidPromoCodeException($enteredCode);
        }
        if ($promoCode->isRegisteredCustomerUserOnly() && $this->currentCustomerUser->findCurrentCustomerUser() === null) {
            throw new AvailableForRegisteredCustomerUserOnly($enteredCode);
        }

        $this->validatePricingGroup($promoCode);
        $this->validatePromoCodeDatetime($promoCode);
        $this->validateRemainigUses($promoCode);
        $this->validatePromoCodeByProductsInCart($promoCode, $cart);
        $this->validatePromoCodeByFlags($promoCode, $cart);
        $this->validateLimit($promoCode, $cart);

        return $promoCode;
    }
}
