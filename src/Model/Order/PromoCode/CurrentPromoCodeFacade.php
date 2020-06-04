<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade as BaseCurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @method \App\Model\Order\PromoCode\PromoCode|null getValidEnteredPromoCodeOrNull()
 */
class CurrentPromoCodeFacade extends BaseCurrentPromoCodeFacade
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductRepository
     */
    private $promoCodeProductRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryRepository
     */
    private $promoCodeCategoryRepository;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory
     */
    private $customerUserIdentifierFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartRepository
     */
    private $cartRepository;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        SessionInterface $session,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository,
        Domain $domain,
        CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        CartRepository $cartRepository
    ) {
        parent::__construct(
            $promoCodeFacade,
            $session
        );
        $this->promoCodeProductRepository = $promoCodeProductRepository;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
        $this->domain = $domain;
        $this->customerUserIdentifierFactory = $customerUserIdentifierFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param string $enteredCode
     */
    public function setEnteredPromoCode($enteredCode)
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($promoCode === null) {
            throw new InvalidPromoCodeException($enteredCode);
        }
        $this->validatePromoCodeDatetime($promoCode);
        $this->validateRemainigUses($promoCode);
        $this->validatePromoCodeByProductsInCart($promoCode);

        $this->session->set(static::PROMO_CODE_SESSION_KEY, $enteredCode);
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
        if ($remainingCodeUses !== null && $remainingCodeUses == 0) {
            throw new \Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException($promoCode->getCode());
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    private function validatePromoCodeByProductsInCart(PromoCode $promoCode)
    {
        $domainId = $this->domain->getId();
        $allowedProductIds = $this->promoCodeProductRepository->getProductIdsByPromoCodeId($promoCode->getId());
        $allowedProductIdsFromCategories = $this->promoCodeCategoryRepository->getProductIdsFromCategoriesByPromoCodeIdAndDomainId($promoCode->getId(), $domainId);

        $allowedProductIds = array_unique(array_merge($allowedProductIds, $allowedProductIdsFromCategories));
        if (count($allowedProductIds) === 0) {
            //promo code hasn't any relation with products or product from categories
            return;
        }

        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);
        $cartItems = $cart === null ? [] : $cart->getItems();

        $isValidPromoCode = false;
        foreach ($cartItems as $cartItem) {
            if (in_array($cartItem->getProduct()->getId(), $allowedProductIds, true) == true) {
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
     * @return \App\Model\Order\PromoCode\PromoCode[]
     */
    public function getPromoCodePerProductByDomainId(array $quantifiedProducts, int $domainId): array
    {
        $validEnteredPromoCode = $this->getValidEnteredPromoCodeOrNull();
        if ($validEnteredPromoCode === null) {
            return [];
        }

        $allowedProductIds = $this->promoCodeProductRepository->getProductIdsByPromoCodeId($validEnteredPromoCode->getId());
        $allowedProductIdsFromCategories = $this->promoCodeCategoryRepository->getProductIdsFromCategoriesByPromoCodeIdAndDomainId($validEnteredPromoCode->getId(), $domainId);

        //todo
        if (count(array_unique(array_merge($allowedProductIds, $allowedProductIdsFromCategories))) === 0) {
            return $this->fillPromoCodeDiscountsForAllProducts($quantifiedProducts, $validEnteredPromoCode);
        }

        $promoCodeDiscountPercentPerProduct = $this->fillPromoCodes($quantifiedProducts, $allowedProductIds, $validEnteredPromoCode);
        $promoCodeDiscountPercentPerProductFromCategories = $this->fillPromoCodes($quantifiedProducts, $allowedProductIdsFromCategories, $validEnteredPromoCode);

        return array_replace($promoCodeDiscountPercentPerProduct, $promoCodeDiscountPercentPerProductFromCategories);
    }

    /**
     * @return string|null
     */
    public function getPromoCodeCode(): ?string
    {
        $validEnteredPromoCode = $this->getValidEnteredPromoCodeOrNull();
        if ($validEnteredPromoCode === null) {
            return null;
        }

        return $validEnteredPromoCode->getCode();
    }

    /**
     * @return string|null
     */
    public function getPromoCodeIdentifier(): ?string
    {
        $validEnteredPromoCode = $this->getValidEnteredPromoCodeOrNull();
        if ($validEnteredPromoCode === null) {
            return null;
        }

        return $validEnteredPromoCode->getIdentifier();
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
            $productId = $quantifiedProduct->getProduct()->getId();
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
            $productId = $quantifiedProduct->getProduct()->getId();
            if (in_array($productId, $allowedProductIds, true)) {
                $promoCodeDiscountPercentPerProduct[(string)$productId] = $validEnteredPromoCode;
            }
        }

        return $promoCodeDiscountPercentPerProduct;
    }
}
