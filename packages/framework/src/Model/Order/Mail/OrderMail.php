<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailDisplayPriceResolver;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade;
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
    public const string VARIABLE_TOTAL_PRICE_WITH_VAT = '{total_price_with_vat}';
    public const string VARIABLE_TOTAL_PRICE_WITHOUT_VAT = '{total_price_without_vat}';
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
        protected readonly PaymentInstructionFacade $paymentInstructionFacade,
        protected readonly MailDisplayPriceResolver $mailDisplayPriceResolver,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    #[Override]
    public function createMessage(MailTemplate $mailTemplate, $order)
    {
        $toEmail = $order->getEmail();
        $bccMail = $mailTemplate->getBccEmail();

        if ($mailTemplate->getOrderStatus()?->getType() === OrderStatusTypeEnum::TYPE_WITHDRAWN) {
            $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

            if ($withdrawalRequest !== null) {
                $bccMail = $this->getBccEmailsForWithdrawal($mailTemplate, $order, $withdrawalRequest);
                $toEmail = $withdrawalRequest->getEmail();
            }
        }

        return new MessageData(
            $toEmail,
            $bccMail,
            $mailTemplate->getBody(),
            $mailTemplate->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $order->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $order->getDomainId()),
            $this->getVariablesReplacementsForBody($order),
            $this->getVariablesReplacementsForSubject($order),
        );
    }

    /**
     * @return string
     */
    public static function getMailTemplateNameByStatus(OrderStatus $orderStatus)
    {
        return static::MAIL_TEMPLATE_NAME_PREFIX . $orderStatus->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate[] $mailTemplates
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
            self::VARIABLE_TOTAL_PRICE_WITH_VAT => $this->getFormattedPriceWithVat($order),
            self::VARIABLE_TOTAL_PRICE_WITHOUT_VAT => $this->getFormattedPriceWithoutVat($order),
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
     * @return array
     */
    protected function getVariablesReplacementsForSubject(Order $order)
    {
        return [
            self::VARIABLE_NUMBER => $order->getNumber(),
            self::VARIABLE_DATE => $this->getFormattedDateTime($order),
        ];
    }

    protected function getFormattedPriceWithVat(Order $order): string
    {
        $price = $this->priceExtension->priceTextWithCurrencyByCurrencyIdAndLocaleFilter(
            $order->getTotalPriceWithVat(),
            $order->getCurrency()->getId(),
            $this->getDomainLocaleByOrder($order),
        );

        return $this->hiddenPriceExtension->hidePriceFilter($price, $order->getCustomerUser());
    }

    protected function getFormattedPriceWithoutVat(Order $order): string
    {
        $price = $this->priceExtension->priceTextWithCurrencyByCurrencyIdAndLocaleFilter(
            $order->getTotalPriceWithoutVat(),
            $order->getCurrency()->getId(),
            $this->getDomainLocaleByOrder($order),
        );

        return $this->hiddenPriceExtension->hidePriceFilter($price, $order->getCustomerUser());
    }

    /**
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
     * @return string
     */
    protected function getDeliveryAddressHtmlTable(Order $order)
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/deliveryAddress.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

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

    protected function getPaymentInstructionsHtml(Order $order): ?string
    {
        $paymentInstructions = $this->paymentInstructionFacade->getPaymentInstructionsForEmail($order);

        if ($paymentInstructions === null) {
            return null;
        }

        return $this->twig->render('@ShopsysFramework/Mail/Order/paymentInstructions.html.twig', [
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'paymentInstructions' => $paymentInstructions,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] $orderItemTotalPricesById
     */
    protected function getProductsHtmlTable(Order $order, array $orderItemTotalPricesById): string
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/products.html.twig', [
            'order' => $order,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'displayPrice' => $this->mailDisplayPriceResolver->getDisplayPrice(),
        ]);
    }

    /**
     * @return string
     */
    protected function getDomainLocaleByOrder(Order $order)
    {
        return $this->domain->getDomainConfigById($order->getDomainId())->getLocale();
    }

    /**
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException
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
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] $orderItemTotalPricesById
     */
    protected function getTransportInfoHtml(Order $order, array $orderItemTotalPricesById): string
    {
        $orderTransportItem = $order->getTransportItem();

        return $this->twig->render('@ShopsysFramework/Mail/Order/transportInfo.html.twig', [
            'order' => $order,
            'orderTransportItem' => $orderTransportItem,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'orderTransportTotalPrice' => $orderItemTotalPricesById[$orderTransportItem->getId()],
            'displayPrice' => $this->mailDisplayPriceResolver->getDisplayPrice(),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] $orderItemTotalPricesById
     */
    protected function getPaymentInfoHtml(Order $order, array $orderItemTotalPricesById): string
    {
        $orderPaymentItem = $order->getPaymentItem();

        return $this->twig->render('@ShopsysFramework/Mail/Order/paymentInfo.html.twig', [
            'order' => $order,
            'orderPaymentItem' => $orderPaymentItem,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'orderPaymentTotalPrice' => $orderItemTotalPricesById[$orderPaymentItem->getId()],
            'displayPrice' => $this->mailDisplayPriceResolver->getDisplayPrice(),
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[] $orderItemTotalPricesById
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

    protected function getAddressesHtml(Order $order): string
    {
        return $this->twig->render('@ShopsysFramework/Mail/Order/addresses.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
        ]);
    }

    /**
     * @return string[]
     */
    protected function getBccEmailsForWithdrawal(
        MailTemplate $mailTemplate,
        Order $order,
        WithdrawalRequest $withdrawalRequest,
    ): array {
        $bccEmails = [$mailTemplate->getBccEmail()];

        if ($order->getEmail() !== $withdrawalRequest->getEmail()) {
            $bccEmails[] = $order->getEmail();
        }

        return array_filter($bccEmails);
    }
}
