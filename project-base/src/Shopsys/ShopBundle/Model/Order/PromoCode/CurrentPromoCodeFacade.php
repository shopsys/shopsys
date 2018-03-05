<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentPromoCodeFacade
{
    const PROMO_CODE_SESSION_KEY = 'promoCode';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    public function __construct(PromoCodeFacade $promoCodeFacade, SessionInterface $session)
    {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->session = $session;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function getValidEnteredPromoCodeOrNull()
    {
        $enteredCode = $this->session->get(self::PROMO_CODE_SESSION_KEY);
        if ($enteredCode === null) {
            return null;
        }

        return $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
    }

    /**
     * @param string $enteredCode
     */
    public function setEnteredPromoCode($enteredCode)
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($promoCode === null) {
            throw new \Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException($enteredCode);
        } else {
            $this->session->set(self::PROMO_CODE_SESSION_KEY, $enteredCode);
        }
    }

    public function removeEnteredPromoCode()
    {
        $this->session->remove(self::PROMO_CODE_SESSION_KEY);
    }
}
