<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use App\Model\Order\PromoCode\PromoCode;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\AlreadyAppliedPromoCodeException;

class CartPromoCodeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
    ) {
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param string $promoCodeCode
     */
    public function applyPromoCodeByCode(Cart $cart, string $promoCodeCode): void
    {
        if ($cart->isPromoCodeApplied($promoCodeCode)) {
            throw new AlreadyAppliedPromoCodeException($promoCodeCode);
        }

        $promoCode = $this->currentPromoCodeFacade->getValidatedPromoCode($promoCodeCode, $cart);

        $cart->applyPromoCode($promoCode);

        $this->em->flush();
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function removePromoCode(Cart $cart, PromoCode $promoCode): void
    {
        $cart->removePromoCodeById($promoCode->getId());

        $this->em->flush();
    }
}
