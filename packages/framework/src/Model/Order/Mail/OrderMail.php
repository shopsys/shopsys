<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Shopsys\FrameworkBundle\Twig\HiddenPriceExtension;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class OrderMail implements MessageFactoryInterface
{
    public const string MAIL_TEMPLATE_NAME_PREFIX = 'order_status_';
    public const string VARIABLE_NUMBER = '{number}';
    public const string VARIABLE_DATE = '{date}';
    public const string VARIABLE_URL = '{url}';
    public const string VARIABLE_TRANSPORT = '{transport}';
    public const string VARIABLE_TRANSPORT_INFO = '{transport_info}';
    public const string VARIABLE_PAYMENT = '{payment}';
    public const string VARIABLE_PAYMENT_INFO = '{payment_info}';
    public const string VARIABLE_TOTAL_PRICE = '{total_price}';
    public const string VARIABLE_BILLING_ADDRESS = '{billing_address}';
    public const string VARIABLE_DELIVERY_ADDRESS = '{delivery_address}';
    public const string VARIABLE_NOTE = '{note}';
    public const string VARIABLE_PRODUCTS = '{products}';
    public const string VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const string VARIABLE_TRANSPORT_INSTRUCTIONS = '{transport_instructions}';
    public const string VARIABLE_PAYMENT_INSTRUCTIONS = '{payment_instructions}';
    public const string VARIABLE_TRANSPORT_TRACKING_NUMBER = '{tracking_number}';
    public const string VARIABLE_TRANSPORT_TRACKING_URL = '{tracking_url}';
    public const string VARIABLE_TRACKING_INSTRUCTIONS = '{tracking_instructions}';
    public const string VARIABLE_ROUNDING_INFO = '{rounding_info}';
    public const string VARIABLE_ADDRESSES = '{addresses}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Twig\Environment $twig
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\FrameworkBundle\Twig\HiddenPriceExtension $hiddenPriceExtension
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly Environment $twig,
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly Domain $domain,
        protected readonly PriceExtension $priceExtension,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly HiddenPriceExtension $hiddenPriceExtension,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $mailTemplate, $order)
    {
        return new MessageData(
            $order->getEmail(),
            $mailTemplate->getBccEmail(),
            $mailTemplate->getBody(),
            $mailTemplate->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $order->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $order->getDomainId()),
            $this->getVariablesReplacementsForBody($order),
            $this->getVariablesReplacementsForSubject($order),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return string
     */
    public static function getMailTemplateNameByStatus(OrderStatus $orderStatus)
    {
        return static::MAIL_TEMPLATE_NAME_PREFIX . $orderStatus->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate[] $mailTemplates
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate|null
     */
    public static function findMailTemplateForOrderStatus(array $mailTemplates, OrderStatus $orderStatus)
    {
        foreach ($mailTemplates as $mailTemplate) {
            if ($mailTemplate->getName() === self::getMailTemplateNameByStatus($orderStatus)) {
                return $mailTemplate;
            }
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return array
     */
    protected function getVariablesReplacementsForBody(Order $order)
    {
        $router = $this->domainRouterFactory->getRouter($order->getDomainId());

        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById(
            $order->getItems(),
        );

        return [
            self::VARIABLE_NUMBER => htmlspecialchars($order->getNumber(), ENT_QUOTES),
            self::VARIABLE_DATE => $this->getFormattedDateTime($order),
            self::VARIABLE_URL => $router->generate('front_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::VARIABLE_TRANSPORT => htmlspecialchars($order->getTransportItem()->getName(), ENT_QUOTES),
            self::VARIABLE_PAYMENT => htmlspecialchars($order->getPaymentItem()->getName(), ENT_QUOTES),
            self::VARIABLE_TOTAL_PRICE => $this->getFormattedPrice($order),
            self::VARIABLE_BILLING_ADDRESS => $this->getBillingAddressHtmlTable($order),
            self::VARIABLE_DELIVERY_ADDRESS => $this->getDeliveryAddressHtmlTable($order),
            self::VARIABLE_NOTE => $this->getNoteHtml($order),
            self::VARIABLE_PRODUCTS => $this->getProductsHtmlTable($order, $orderItemTotalPricesById),
            self::VARIABLE_ORDER_DETAIL_URL => $this->orderUrlGenerator->getOrderDetailUrl($order),
            self::VARIABLE_TRANSPORT_INSTRUCTIONS => $this->getTransportInstructionsHtml($order),
            self::VARIABLE_PAYMENT_INSTRUCTIONS => $this->getPaymentInstructionsHtml($order),
            self::VARIABLE_TRACKING_INSTRUCTIONS => $this->getTrackingInstructions($order),
            self::VARIABLE_TRANSPORT_INFO => $this->getTransportInfoHtml($order, $orderItemTotalPricesById),
            self::VARIABLE_PAYMENT_INFO => $this->getPaymentInfoHtml($order, $orderItemTotalPricesById),
            self::VARIABLE_ROUNDING_INFO => $this->getRoundingInfoHtml($order, $orderItemTotalPricesById),
            self::VARIABLE_ADDRESSES => $this->getAddressesHtml($order),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return array
     */
    protected function getVariablesReplacementsForSubject(Order $order)
    {
        return [
            self::VARIABLE_NUMBER => $order->getNumber(),
            self::VARIABLE_DATE => $this->getFormattedDateTime($order),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getFormattedPrice(Order $order): string
    {
        $price = $this->priceExtension->priceTextWithCurrencyByCurrencyIdAndLocaleFilter(
            $order->getTotalPriceWithVat(),
            $order->getCurrency()->getId(),
            $this->getDomainLocaleByOrder($order),
        );

        return $this->hiddenPriceExtension->hidePriceFilter($price, $order->getCustomerUser());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getFormattedDateTime(Order $order)
    {
        return $this->dateTimeFormatterExtension->formatDateTime(
            $order->getCreatedAt(),
            $this->getDomainLocaleByOrder($order),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getBillingAddressHtmlTable(Order $order)
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/billingAddress.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getDeliveryAddressHtmlTable(Order $order)
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/deliveryAddress.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string|null
     */
    protected function getNoteHtml(Order $order): ?string
    {
        if ($order->getNote() === null) {
            return null;
        }

        return $this->twig->render('@ShopsysFramework/Mail/Order/note.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string|null
     */
    protected function getTransportInstructionsHtml(Order $order): ?string
    {
        if ($order->getTransportItem()->getTransport()->getInstructions($this->getDomainLocaleByOrder($order)) === null) {
            return null;
        }

        return $this->twig->render('@ShopsysFramework/Mail/Order/transportInstructions.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string|null
     */
    protected function getPaymentInstructionsHtml(Order $order): ?string
    {
        if ($order->getPaymentItem()->getPayment()->getInstructions($this->getDomainLocaleByOrder($order)) === null) {
            return null;
        }

        return $this->twig->render('@ShopsysFramework/Mail/Order/paymentInstructions.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $orderItemTotalPricesById
     * @return string
     */
    protected function getProductsHtmlTable(Order $order, array $orderItemTotalPricesById): string
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/products.html.twig', [
            'order' => $order,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getDomainLocaleByOrder(Order $order)
    {
        return $this->domain->getDomainConfigById($order->getDomainId())->getLocale();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException
     * @return string|null
     */
    protected function getTrackingInstructions(Order $order): ?string
    {
        $orderDomainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $transport = $order->getTransportItem()->getTransport();

        $trackingInstructions = $transport->getTrackingInstruction($orderDomainConfig->getLocale());
        $trackingUrl = $order->getTrackingUrl();
        $trackingNumber = $order->getTrackingNumber();

        if ($trackingInstructions === null || $trackingUrl === null || $trackingNumber === null) {
            return null;
        }

        return strtr($trackingInstructions, [
            self::VARIABLE_TRANSPORT_TRACKING_NUMBER => $trackingNumber,
            self::VARIABLE_TRANSPORT_TRACKING_URL => $trackingUrl,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $orderItemTotalPricesById
     * @return string
     */
    protected function getTransportInfoHtml(Order $order, array $orderItemTotalPricesById): string
    {
        $orderTransportItem = $order->getTransportItem();

        return $this->twig->render('@ShopsysFramework/Mail/Order/transportInfo.html.twig', [
            'order' => $order,
            'orderTransportItem' => $orderTransportItem,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'orderTransportTotalPrice' => $orderItemTotalPricesById[$orderTransportItem->getId()],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $orderItemTotalPricesById
     * @return string
     */
    protected function getPaymentInfoHtml(Order $order, array $orderItemTotalPricesById): string
    {
        $orderPaymentItem = $order->getPaymentItem();

        return $this->twig->render('@ShopsysFramework/Mail/Order/paymentInfo.html.twig', [
            'order' => $order,
            'orderPaymentItem' => $orderPaymentItem,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'orderPaymentTotalPrice' => $orderItemTotalPricesById[$orderPaymentItem->getId()],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $orderItemTotalPricesById
     * @return string|null
     */
    protected function getRoundingInfoHtml(Order $order, array $orderItemTotalPricesById): ?string
    {
        $orderRoundingItems = $order->getRoundingItems();

        if (count($orderRoundingItems) === 0) {
            return null;
        }

        $orderRoundingItem = reset($orderRoundingItems);

        return $this->twig->render('@ShopsysFramework/Mail/Order/roundingInfo.html.twig', [
            'order' => $order,
            'orderRoundingItem' => $orderRoundingItem,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'orderRoundingTotalPrice' => $orderItemTotalPricesById[$orderRoundingItem->getId()],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getAddressesHtml(Order $order): string
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/addresses.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }
}
