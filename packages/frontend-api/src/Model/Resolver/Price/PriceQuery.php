<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use ArrayObject;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrontendApiBundle\Component\GqlContext\GqlContextHelper;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Price\PriceFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Price\Exception\ProductPriceMissingUserError;

class PriceQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     */
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly PriceFacade $priceFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly OrderApiFacade $orderApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function priceByProductQuery($data): ProductPrice
    {
        if ($data instanceof Product) {
            $productPrice = $this->productCachedAttributesFacade->getProductSellingPrice($data);
        } else {
            $productPrice = $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
        }

        if ($productPrice === null) {
            throw new ProductPriceMissingUserError('The product price is not set.');
        }

        return $productPrice;
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
        );

        return $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateIndependentPaymentPrice(Payment $payment): Price
    {
        return $this->paymentPriceCalculation->calculateIndependentPrice(
            $payment,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
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
        );

        return $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateIndependentTransportPrice(Transport $transport): Price
    {
        return $this->transportPriceCalculation->calculateIndependentPrice(
            $transport,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
        );
    }
}
