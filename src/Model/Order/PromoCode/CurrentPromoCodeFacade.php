<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade as BaseCurrentPromoCodeFacade;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade, \Symfony\Component\HttpFoundation\Session\SessionInterface $session)
 * @method \App\Model\Order\PromoCode\PromoCode|null getValidEnteredPromoCodeOrNull()
 */
class CurrentPromoCodeFacade extends BaseCurrentPromoCodeFacade
{
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
    private function validatePromoCodeDatetime(PromoCode $promoCode)
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
}
