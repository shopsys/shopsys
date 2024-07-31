<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\AlreadyAppliedPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrontendApiBundle\Model\Cart\WhateverOrderCartFacade;

class CartPromoCodeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly WhateverOrderCartFacade $whateverOrderCartFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @param string $promoCodeCode
     */
    public function applyPromoCodeByCode(Order $cart, string $promoCodeCode): void
    {
        if ($cart->isPromoCodeApplied($promoCodeCode)) {
            throw new AlreadyAppliedPromoCodeException($promoCodeCode);
        }

        $promoCode = $this->currentPromoCodeFacade->getValidatedPromoCode($promoCodeCode, $cart);

        $cart->applyPromoCode($promoCode);

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function removePromoCode(Order $cart, PromoCode $promoCode): void
    {
        $cart->removePromoCodeById($promoCode->getId());

        $this->em->flush();
    }
}
