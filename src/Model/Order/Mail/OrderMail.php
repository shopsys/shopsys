<?php

declare(strict_types=1);

namespace App\Model\Order\Mail;

use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail as BaseOrderMail;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property \App\Component\Setting\Setting $setting
 * @method __construct(\App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Twig_Environment $twig, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension, \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension, \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator)
 * @method \Shopsys\FrameworkBundle\Model\Mail\MessageData createMessage(\Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate, \App\Model\Order\Order $order)
 * @method array getVariablesReplacementsForSubject(\App\Model\Order\Order $order)
 * @method string getFormattedPrice(\App\Model\Order\Order $order)
 * @method string getFormattedDateTime(\App\Model\Order\Order $order)
 * @method string getBillingAddressHtmlTable(\App\Model\Order\Order $order)
 * @method string getDeliveryAddressHtmlTable(\App\Model\Order\Order $order)
 * @method string getProductsHtmlTable(\App\Model\Order\Order $order)
 * @method string getDomainLocaleByOrder(\App\Model\Order\Order $order)
 * @method __construct(\App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Twig\Environment $twig, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension, \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension, \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator)
 */
class OrderMail extends BaseOrderMail
{
    /**
     * @param \App\Model\Order\Order $order
     * @return string[]
     */
    protected function getVariablesReplacementsForBody(Order $order)
    {
        $router = $this->domainRouterFactory->getRouter($order->getDomainId());
        $orderDomainConfig = $this->domain->getDomainConfigById($order->getDomainId());

        $payment = $order->getPayment();
        $paymentInstructions = $payment->getInstructions($orderDomainConfig->getLocale());

        $transportsInstructions = [];
        foreach ($order->getTransports() as $transport) {
            $transportsInstructions[] = $transport->getInstructions($orderDomainConfig->getLocale());
        }

        return [
            self::VARIABLE_NUMBER => htmlspecialchars($order->getNumber(), ENT_QUOTES),
            self::VARIABLE_DATE => $this->getFormattedDateTime($order),
            self::VARIABLE_URL => $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::VARIABLE_TRANSPORT => htmlspecialchars($order->getTransportName(), ENT_QUOTES),
            self::VARIABLE_PAYMENT => htmlspecialchars($order->getPaymentName(), ENT_QUOTES),
            self::VARIABLE_TOTAL_PRICE => $this->getFormattedPrice($order),
            self::VARIABLE_BILLING_ADDRESS => $this->getBillingAddressHtmlTable($order),
            self::VARIABLE_DELIVERY_ADDRESS => $this->getDeliveryAddressHtmlTable($order),
            self::VARIABLE_NOTE => htmlspecialchars((string)$order->getNote(), ENT_QUOTES),
            self::VARIABLE_PRODUCTS => $this->getProductsHtmlTable($order),
            self::VARIABLE_ORDER_DETAIL_URL => $this->orderUrlGenerator->getOrderDetailUrl($order),
            self::VARIABLE_TRANSPORT_INSTRUCTIONS => implode('<br /> ', $transportsInstructions),
            self::VARIABLE_PAYMENT_INSTRUCTIONS => $paymentInstructions,
        ];
    }
}
