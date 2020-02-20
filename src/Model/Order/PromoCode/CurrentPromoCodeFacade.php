<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade as BaseCurrentPromoCodeFacade;
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
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        SessionInterface $session,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository
    ) {
        parent::__construct(
            $promoCodeFacade,
            $session
        );
        $this->promoCodeProductRepository = $promoCodeProductRepository;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
    }

    /**
     * @param string $enteredCode
     */
    public function setEnteredPromoCode($enteredCode)
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($promoCode === null) {
            throw new \Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException($enteredCode);
        }
        $this->validatePromoCodeDatetime($promoCode);

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
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @return array
     */
    public function getPromoCodeDiscountPercentPerProductByDomainId(array $quantifiedProducts, int $domainId): array
    {
        $validEnteredPromoCode = $this->getValidEnteredPromoCodeOrNull();
        if ($validEnteredPromoCode === null) {
            return [];
        }

        $allowedProducts = $this->promoCodeProductRepository->getProductIdsByPromoCodeId($validEnteredPromoCode->getId());
        $promoCodeDiscountPercentPerProduct = $this->fillPromoCodeDiscounts($quantifiedProducts, $allowedProducts, $validEnteredPromoCode);

        $allowedProductsFromCategories = $this->promoCodeCategoryRepository->getProductsFromCategoriesByPromoCodeIdAndDomainId($validEnteredPromoCode->getId(), $domainId);
        $promoCodeDiscountPercentPerProductFromCategories = $this->fillPromoCodeDiscounts($quantifiedProducts, $allowedProductsFromCategories, $validEnteredPromoCode);

        return array_replace($promoCodeDiscountPercentPerProduct, $promoCodeDiscountPercentPerProductFromCategories);
    }

    /**
     * @param array $quantifiedProducts
     * @param array $allowedProducts
     * @param \App\Model\Order\PromoCode\PromoCode $validEnteredPromoCode
     * @return array
     */
    private function fillPromoCodeDiscounts(array $quantifiedProducts, array $allowedProducts, PromoCode $validEnteredPromoCode): array
    {
        $promoCodeDiscountPercentPerProduct = [];
        foreach ($quantifiedProducts as $quantifiedProduct) {
            $productId = $quantifiedProduct->getProduct()->getId();
            if (in_array($productId, $allowedProducts, true)) {
                $promoCodeDiscountPercentPerProduct[(string)$productId] = $validEnteredPromoCode->getPercent();
            }
        }

        return $promoCodeDiscountPercentPerProduct;
    }
}
