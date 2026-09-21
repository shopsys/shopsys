<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use ArrayObject;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider;
use Shopsys\FrontendApiBundle\Component\GqlContext\GqlContextHelper;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Transport\Exception\TransportPriceMissingUserError;

class PriceQuery extends AbstractQuery
{
    protected const string CART_CACHE_NAMESPACE = 'priceQueryCart';

    public function __construct(
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly TransportPriceProvider $transportPriceProvider,
        protected readonly PaymentPriceProvider $paymentPriceProvider,
        protected readonly GqlContextHelper $gqlContextHelper,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function priceByPaymentQuery(
        Payment $payment,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): PriceInterface {
        $cartUuid ??= $this->gqlContextHelper->getCartUuid($context);
        $orderUuid = $this->gqlContextHelper->getOrderUuid($context);

        if ($cartUuid === null && $orderUuid !== null) {
            $order = $this->orderApiFacade->getByUuid($orderUuid);

            return $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $order->getTotalProductsPrice(),
                $order->getDomainId(),
                $order->isFreeTransportAndPaymentApplied(),
                $order->getCurrencyRoundingType(),
                $order->getCurrencyRoundingPlacesPriceWithoutVat(),
            );
        }

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->calculateIndependentPaymentPrice($payment);
        }

        $cart = $this->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->calculateIndependentPaymentPrice($payment);
        }

        return $this->paymentPriceProvider->getPaymentPrice($cart, $payment, $this->domain->getCurrentDomainConfig());
    }

    protected function calculateIndependentPaymentPrice(Payment $payment): PriceInterface
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->paymentPriceCalculation->calculateIndependentPrice(
            $payment,
            $this->domain->getId(),
            $currency->getRoundingType(),
            $currency->getRoundingPlacesPriceWithoutVat(),
        );
    }

    public function priceByTransportQuery(
        Transport $transport,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): PriceInterface {
        $cartUuid ??= $this->gqlContextHelper->getCartUuid($context);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->calculateIndependentTransportPrice($transport);
        }

        $cart = $this->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->calculateIndependentTransportPrice($transport);
        }

        try {
            return $this->transportPriceProvider->getTransportPrice($cart, $transport, $this->domain->getCurrentDomainConfig());
        } catch (TransportPriceNotFoundException) {
            throw new TransportPriceMissingUserError('The transport has no price for the given cart, e.g. because the cart exceeds its weight limit.');
        }
    }

    protected function findCart(?CustomerUser $customerUser, ?string $cartUuid): ?Cart
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::CART_CACHE_NAMESPACE,
            fn (): ?Cart => $this->cartApiFacade->findCart($customerUser, $cartUuid),
            $customerUser?->getId() ?? 'anonymous',
            $cartUuid ?? 'currentCustomerCart',
        );
    }

    protected function calculateIndependentTransportPrice(Transport $transport): PriceInterface
    {
        return $this->transportPriceCalculation->calculateIndependentPrice(
            $transport->getLowestPriceOnDomain($this->domain->getId()),
        );
    }
}
