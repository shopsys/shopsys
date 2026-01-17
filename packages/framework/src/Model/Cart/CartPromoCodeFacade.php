<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\AlreadyAppliedPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class CartPromoCodeFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
    ) {
    }

    public function applyPromoCodeByCode(Cart $cart, string $promoCodeCode): void
    {
        if ($cart->isPromoCodeApplied($promoCodeCode)) {
            throw new AlreadyAppliedPromoCodeException($promoCodeCode);
        }

        $promoCode = $this->currentPromoCodeFacade->getValidatedPromoCode($promoCodeCode, $cart);

        $cart->applyPromoCode($promoCode);

        $this->em->flush();
    }

    public function removePromoCode(Cart $cart, PromoCode $promoCode): void
    {
        $cart->removePromoCodeById($promoCode->getId());

        $this->em->flush();
    }
}
