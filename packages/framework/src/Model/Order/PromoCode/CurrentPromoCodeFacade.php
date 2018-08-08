<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentPromoCodeFacade
{
    const PROMO_CODE_SESSION_KEY = 'promoCode';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade
     */
    protected $promoCodeFacade;

    public function __construct(PromoCodeFacade $promoCodeFacade, SessionInterface $session)
    {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->session = $session;
    }

    public function getValidEnteredPromoCodeOrNull(): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        $enteredCode = $this->session->get(self::PROMO_CODE_SESSION_KEY);
        if ($enteredCode === null) {
            return null;
        }

        return $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
    }

    public function setEnteredPromoCode(string $enteredCode): void
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($promoCode === null) {
            throw new \Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException($enteredCode);
        } else {
            $this->session->set(self::PROMO_CODE_SESSION_KEY, $enteredCode);
        }
    }

    public function removeEnteredPromoCode(): void
    {
        $this->session->remove(self::PROMO_CODE_SESSION_KEY);
    }
}
