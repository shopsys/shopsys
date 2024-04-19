<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Price;

use ArrayObject;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Component\GqlContext\GqlContextHelper;
use Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceQuery as BasePriceQuery;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @property \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade, \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory, \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade, \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice priceByProductQuery(\App\Model\Product\Product|array $data)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculateIndependentPaymentPrice(\App\Model\Payment\Payment $payment)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculateIndependentTransportPrice(\App\Model\Transport\Transport $transport)
 */
class PriceQuery extends BasePriceQuery
{
    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     * @param \ArrayObject|null $context
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function priceByTransportQuery(
        Transport $transport,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): Price {
        $cartUuid = $cartUuid ?? GqlContextHelper::getCartUuid($context);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->calculateIndependentTransportPrice($transport);
        }

        /** @var \App\Model\Cart\Cart $cart */
        $cart = $this->cartApiFacade->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->calculateIndependentTransportPrice($transport);
        }

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            null,
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        return $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     * @param \ArrayObject|null $context
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function priceByPaymentQuery(
        Payment $payment,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): Price {
        $cartUuid = $cartUuid ?? GqlContextHelper::getCartUuid($context);
        $orderUuid = GqlContextHelper::getOrderUuid($context);

        if ($cartUuid === null && $orderUuid !== null) {
            $order = $this->orderApiFacade->getByUuid($orderUuid);

            return $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $order->getCurrency(),
                $order->getTotalProductsPrice(),
                $order->getDomainId(),
            );
        }

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->calculateIndependentPaymentPrice($payment);
        }

        /** @var \App\Model\Cart\Cart $cart */
        $cart = $this->cartApiFacade->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->calculateIndependentPaymentPrice($payment);
        }

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            null,
            $payment,
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        return $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );
    }
}
