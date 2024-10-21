<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Order\ConvertimOrderData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;

class ConvertimOrderDataToCartMapper
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\Payment\CartPaymentDataFactory $cartPaymentDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportDataFactory $cartTransportDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly ProductFacade $productFacade,
        protected readonly CartFacade $cartFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly Domain $domain,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly CartPaymentDataFactory $cartPaymentDataFactory,
        protected readonly TransportFacade $transportFacade,
        protected readonly CartTransportDataFactory $cartTransportDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function mapConvertimOrderDataToCart(ConvertimOrderData $convertimOrderData): Cart
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            $convertimOrderData->getCustomerData()->getEmail(),
            $this->domain->getId(),
        );

        $cart = $this->cartApiFacade->getCartCreateIfNotExists(null, null);

        foreach ($convertimOrderData->getOrderItemsData() as $orderItemData) {
            $product = $this->productFacade->getByUuid($orderItemData->getProductId());
            $result = $this->cartFacade->addProductToExistingCart($product, (int)$orderItemData->getQuantity(), $cart, true);
            $result->getCartItem()->setWatchedPrice(Money::create($orderItemData->getPriceWithVat()));
        }

        $this->applyConvertimPromoCodesToCart($convertimOrderData->getPromoCodes(), $cart);
        $this->applyConvertimPaymentToCart($convertimOrderData, $cart);
        $this->applyConvertimTransportToCart($convertimOrderData, $cart);
        $cart->setPaymentWatchedPrice(Money::create($convertimOrderData->getPaymentData()->getPriceWithVat()));
        $cart->setTransportWatchedPrice(Money::create($convertimOrderData->getTransportData()->getPriceWithVat()));
        $cart->assignCartToCustomerUser($customerUser);

        return $cart;
    }

    /**
     * @param array $convertimOrderPromoCodesData
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function applyConvertimPromoCodesToCart(array $convertimOrderPromoCodesData, Cart $cart): void
    {
        foreach ($convertimOrderPromoCodesData as $convertimOrderPromoCodeData) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($convertimOrderPromoCodeData->getCode(), $this->domain->getId());

            if ($promoCode !== null) {
                $cart->applyPromoCode($promoCode);
            }
        }
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function applyConvertimPaymentToCart(ConvertimOrderData $convertimOrderData, Cart $cart): void
    {
        $payment = $this->paymentFacade->getByUuid($convertimOrderData->getPaymentData()->getUuid());
        $cartPaymentData = $this->cartPaymentDataFactory->create($cart, $payment->getUuid(), null);
        $cartPaymentData->watchedPrice = Money::create($convertimOrderData->getPaymentData()->getPriceWithVat());

        $cart->editCartPayment($cartPaymentData);
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function applyConvertimTransportToCart(ConvertimOrderData $convertimOrderData, Cart $cart): void
    {
        $transport = $this->transportFacade->getByUuid($convertimOrderData->getTransportData()->getUuid());
        $cartTransportData = $this->cartTransportDataFactory->create($cart, $transport->getUuid(), null);
        $cartTransportData->watchedPrice = Money::create($convertimOrderData->getTransportData()->getPriceWithVat());

        $cart->editCartTransport($cartTransportData);
    }
}
