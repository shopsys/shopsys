<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use App\Model\Order\Order;
use League\ISO3166\ISO3166;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoPayOrderMapper
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade
     */
    private $goPayPaymentMethodFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     */
    public function __construct(
        DomainRouterFactory $domainRouterFactory,
        GoPayPaymentMethodFacade $goPayPaymentMethodFacade
    ) {
        $this->domainRouterFactory = $domainRouterFactory;
        $this->goPayPaymentMethodFacade = $goPayPaymentMethodFacade;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string|null $goPayBankSwift
     * @return array
     */
    public function createGoPayPaymentData(Order $order, ?string $goPayBankSwift): array
    {
        $orderPayment = $order->getPayment();
        $defaultPaymentInstrument = $orderPayment->getGoPayPaymentMethod() !== null ? $orderPayment->getGoPayPaymentMethod()->getIdentifier() : '';

        $goPayPaymentItemsData = $this->createGoPayPaymentItemsData($order);
        $router = $this->domainRouterFactory->getRouter($order->getDomainId());
        $payment = [
            'payer' => [
                'default_payment_instrument' => $defaultPaymentInstrument,
                'allowed_payment_instruments' => $this->goPayPaymentMethodFacade->getAllTypeIdentifiers(),
                'contact' => $this->createContactData($order),
            ],
            'amount' => $this->formatPriceForGoPay($order->getTotalPriceWithVat()),
            'currency' => $order->getCurrency()->getCode(),
            'order_number' => $order->getNumber(),
            'order_description' => t('Order number') . ' ' . $order->getNumber(),
            'items' => $goPayPaymentItemsData,
            'callback' => [
                'return_url' => $router->generate(
                    'front_order_paid',
                    ['orderIdentifier' => $order->getUuid()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'notification_url' => $router->generate(
                    'front_order_payment_status_notify',
                    ['orderIdentifier' => $order->getUuid()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ],
        ];

        if ($goPayBankSwift !== null) {
            $payment['payer']['default_swift'] = $goPayBankSwift;

            return $payment;
        }

        return $payment;
    }

    /**
     * GoPay requires prices in cents.
     *
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return int
     */
    public function formatPriceForGoPay(Money $price): int
    {
        return (int)round((float)$price->multiply(100)->getAmount());
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return array
     */
    private function createGoPayPaymentItemsData(Order $order): array
    {
        $orderItems = [];
        foreach ($order->getItems() as $orderItem) {
            $orderItems[] = [
                'name' => $orderItem->getName(),
                'amount' => $this->formatPriceForGoPay($orderItem->getTotalPriceWithVat()),
                'count' => $orderItem->getQuantity(),
            ];
        }

        return $orderItems;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return string[]
     */
    private function createContactData(Order $order): array
    {
        $contact = [
            'first_name' => $order->getDeliveryFirstName(),
            'last_name' => $order->getDeliveryLastName(),
            'email' => $order->getEmail(),
            'phone_number' => $order->getDeliveryTelephone(),
        ];

        if ($order->getCity() !== null) {
            $contact['city'] = $order->getCity();
        }

        if ($order->getStreet() !== null) {
            $contact['street'] = $order->getStreet();
        }

        if ($order->getPostcode() !== null) {
            $contact['postal_code'] = $order->getPostcode();
        }

        if ($order->getCountry() !== null) {
            $countryIso3166 = new ISO3166();
            $countryData = $countryIso3166->alpha2($order->getCountry()->getCode());

            $contact['country_code'] = $countryData['alpha3'];
        }

        return $contact;
    }
}
