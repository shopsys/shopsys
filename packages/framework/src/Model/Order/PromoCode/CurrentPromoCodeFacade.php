<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentPromoCodeFacade
{
    protected const PROMO_CODE_SESSION_KEY = 'promoCode';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade
     */
    protected $promoCodeFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(PromoCodeFacade $promoCodeFacade, SessionInterface $session)
    {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->session = $session;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function getValidEnteredPromoCodeOrNull(): ?\Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
    {
        $enteredCode = $this->session->get(static::PROMO_CODE_SESSION_KEY);
        if ($enteredCode === null) {
            return null;
        }

        return $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
    }

    /**
     * @param string $enteredCode
     */
    public function setEnteredPromoCode(string $enteredCode): void
    {
        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($enteredCode);
        if ($promoCode === null) {
            throw new InvalidPromoCodeException($enteredCode);
        }
        $this->session->set(static::PROMO_CODE_SESSION_KEY, $enteredCode);
    }

    public function removeEnteredPromoCode(): void
    {
        $this->session->remove(static::PROMO_CODE_SESSION_KEY);
    }
}
