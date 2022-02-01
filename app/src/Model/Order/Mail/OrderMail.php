<?php

declare(strict_types=1);

namespace App\Model\Order\Mail;

use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail as BaseOrderMail;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;

/**
 * @property \App\Component\Setting\Setting $setting
 * @method \Shopsys\FrameworkBundle\Model\Mail\MessageData createMessage(\App\Model\Mail\MailTemplate $mailTemplate, \App\Model\Order\Order $order)
 * @method array getVariablesReplacementsForSubject(\App\Model\Order\Order $order)
 * @method string getFormattedPrice(\App\Model\Order\Order $order)
 * @method string getFormattedDateTime(\App\Model\Order\Order $order)
 * @method string getDeliveryAddressHtmlTable(\App\Model\Order\Order $order)
 * @method string getProductsHtmlTable(\App\Model\Order\Order $order)
 * @method string getDomainLocaleByOrder(\App\Model\Order\Order $order)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Twig\PriceExtension $priceExtension
 * @property \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
 * @method static \App\Model\Mail\MailTemplate|null findMailTemplateForOrderStatus(\App\Model\Mail\MailTemplate[] $mailTemplates, \App\Model\Order\Status\OrderStatus $orderStatus)
 * @method __construct(\App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Twig\Environment $twig, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension, \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension, \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator)
 * @method static string getMailTemplateNameByStatus(\App\Model\Order\Status\OrderStatus $orderStatus)
 */
class OrderMail extends BaseOrderMail
{
    public const TRANSPORT_VARIABLE_TRACKING_NUMBER = '{tracking_number}';
    public const TRANSPORT_VARIABLE_TRACKING_URL = '{tracking_url}';
    public const VARIABLE_TRACKING_INSTRUCTIONS = '{tracking_instructions}';

    /**
     * @param \App\Model\Order\Order $order
     * @return string
     */
    protected function getBillingAddressHtmlTable(BaseOrder $order)
    {
        return $this->twig->render('Front/Mail/Order/billingAddress.html.twig', [
            'order' => $order,
            'orderLocale' => $this->getDomainLocaleByOrder($order),
            'domain' => $this->domain,
        ]);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return array
     */
    protected function getVariablesReplacementsForBody(BaseOrder $order): array
    {
        $variableReplacements = parent::getVariablesReplacementsForBody($order);

        $variableReplacements[self::VARIABLE_TRACKING_INSTRUCTIONS] = $this->getTrackingInstructions($order);

        return $variableReplacements;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @throws \Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException
     * @return string|null
     */
    private function getTrackingInstructions(Order $order): ?string
    {
        $orderDomainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $transport = $order->getTransport();

        $trackingInstructions = $transport->getTrackingInstruction($orderDomainConfig->getLocale());
        $trackingUrl = $order->getTrackingUrl();
        $trackingNumber = $order->getTrackingNumber();

        if ($trackingInstructions === null || $trackingUrl === null || $trackingNumber === null) {
            return null;
        }

        return strtr($trackingInstructions, [
            self::TRANSPORT_VARIABLE_TRACKING_NUMBER => $trackingNumber,
            self::TRANSPORT_VARIABLE_TRACKING_URL => $trackingUrl,
        ]);
    }
}
