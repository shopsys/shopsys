<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\HttpFoundation\RequestStack;

class CurrentPromoCodeFacade
{
    protected const PROMO_CODE_SESSION_KEY = 'promoCode';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public function getValidEnteredPromoCodeOrNull(): ?PromoCode
    {
        $enteredCode = $this->requestStack->getSession()->get(static::PROMO_CODE_SESSION_KEY);

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
        $this->requestStack->getSession()->set(static::PROMO_CODE_SESSION_KEY, $enteredCode);
    }

    public function removeEnteredPromoCode(): void
    {
        $this->requestStack->getSession()->remove(static::PROMO_CODE_SESSION_KEY);
    }
}
