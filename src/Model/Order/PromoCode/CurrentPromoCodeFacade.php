<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

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
        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($this->isValidPromoCode($promoCode)) {
            $this->session->set(static::PROMO_CODE_SESSION_KEY, $enteredCode);
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException($enteredCode);
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return bool
     */
    private function isValidPromoCode(?PromoCode $promoCode = null): bool
    {
        $currentTimestamp = time();

        if ($promoCode) {
            if ($promoCode->getDatetimeValidFrom() == null
                && $promoCode->getDatetimeValidTo() == null
            ) {
                return true;
            }

            if ($promoCode->getDatetimeValidFrom() != null
                && $promoCode->getDatetimeValidTo() != null
            ) {
                if ($promoCode->getDatetimeValidFrom()->getTimestamp() < $currentTimestamp
                    && $promoCode->getDatetimeValidTo()->getTimestamp() > $currentTimestamp
                ) {
                    return true;
                } else {
                    return false;
                }
            }

            if ($promoCode->getDatetimeValidFrom() != null && $promoCode->getDatetimeValidFrom()->getTimestamp() < $currentTimestamp) {
                return true;
            }

            if ($promoCode->getDatetimeValidTo() != null && $promoCode->getDatetimeValidTo()->getTimestamp() > $currentTimestamp) {
                return true;
            }
        }
        return false;
    }
}
