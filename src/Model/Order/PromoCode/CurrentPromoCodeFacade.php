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

    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        SessionInterface $session,
        PromoCodeProductRepository $promoCodeProductRepository
    )
    {
        parent::__construct(
            $promoCodeFacade,
            $session
        );
        $this->promoCodeProductRepository = $promoCodeProductRepository;
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
     */
    public function getPromoCodeDiscountPercentPerProduct(array $quantifiedProducts): array
    {
        $validEnteredPromoCode = $this->getValidEnteredPromoCodeOrNull();
        if($validEnteredPromoCode === null){
            return [];
        }

        $allowedProducts = $this->promoCodeProductRepository->getProductIdsByPromoCodeId($validEnteredPromoCode->getId());

        $promoCodeDiscountPercentPerProduct = [];
        foreach ($quantifiedProducts as $quantifiedProduct){
            $productId = $quantifiedProduct->getProduct()->getId();
            if(in_array($productId, $allowedProducts)){
                $promoCodeDiscountPercentPerProduct[$productId] = $validEnteredPromoCode->getPercent();
            }

        }
        return $promoCodeDiscountPercentPerProduct;
    }
}
