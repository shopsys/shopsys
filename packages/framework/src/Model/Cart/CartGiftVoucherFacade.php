<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Exception\GiftVoucherNotFoundException;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;

class CartGiftVoucherFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GiftVoucherFacade $giftVoucherFacade,
    ) {
    }

    public function applyGiftVoucherByCode(Cart $cart, string $enteredCode): void
    {
        $giftVoucher = $this->giftVoucherFacade->findByCode($enteredCode);

        if ($giftVoucher === null) {
            throw new GiftVoucherNotFoundException('Gift voucher with code "' . $enteredCode . '" not found.');
        }

        $cart->applyGiftVoucher($giftVoucher);

        $this->em->flush();
    }

    public function removeGiftVoucherByCode(Cart $cart, string $enteredCode): void
    {
        $giftVoucher = $this->giftVoucherFacade->findByCode($enteredCode);

        if ($giftVoucher === null || !$cart->isGiftVoucherApplied($giftVoucher->getCode())) {
            return;
        }

        $cart->removeGiftVoucherById($giftVoucher->getId());

        $this->em->flush();
    }
}
