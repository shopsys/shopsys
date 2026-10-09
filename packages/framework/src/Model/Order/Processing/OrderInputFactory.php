<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput as BaseOrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\Preloader\OrderInputPreloaderFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class OrderInputFactory
{
    public function __construct(
        protected readonly OrderInputPreloaderFacade $orderInputPreloaderFacade,
    ) {
    }

    public function create(DomainConfig $domainConfig): OrderInput
    {
        return new OrderInput($domainConfig);
    }

    public function createFromCart(Cart $cart, DomainConfig $domainConfig): OrderInput
    {
        $orderInput = $this->create($domainConfig);

        $this->fillItemsByCart($orderInput, $cart);
        $orderInput->setCustomerUser($cart->getCustomerUser());

        // products are batch-loaded before the cart is weighed, which avoids the N+1 problem when reading their weights
        $this->orderInputPreloaderFacade->preload($orderInput);

        $orderInput->setPayment($cart->getPayment());
        $orderInput->setTransport($cart->getTransport());

        $orderInput->addAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER, $cart->getPickupPlaceIdentifier());
        $orderInput->addAdditionalData(AddPaymentMiddleware::ADDITIONAL_DATA_GOPAY_BANK_SWIFT, $cart->getPaymentGoPayBankSwift());
        $orderInput->addAdditionalData(AddTransportMiddleware::ADDITIONAL_DATA_CART_TOTAL_WEIGHT, $cart->getTotalWeight());

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            $orderInput->addPromoCode($promoCode);
        }

        foreach ($cart->getAllAppliedGiftVouchers() as $giftVoucher) {
            $orderInput->addGiftVoucher($giftVoucher);
        }

        return $orderInput;
    }

    public function createForSingleProduct(
        Product $product,
        DomainConfig $domainConfig,
        ?CustomerUser $customerUser = null,
    ): OrderInput {
        $orderInput = $this->create($domainConfig);

        $orderInput->addProduct($product, 1);

        $orderInput->setCustomerUser($customerUser);
        $orderInput->addAdditionalData(
            AddTransportMiddleware::ADDITIONAL_DATA_CART_TOTAL_WEIGHT,
            $product->getWeight() ?? 0,
        );

        return $orderInput;
    }

    protected function fillItemsByCart(BaseOrderInput $orderInput, Cart $cart): void
    {
        $orderInput->cleanProducts();

        foreach ($cart->getQuantifiedProducts() as $quantifiedProduct) {
            $orderInput->addQuantifiedProduct($quantifiedProduct);
        }
    }
}
