<?php

declare(strict_types=1);

namespace App\Model\Order\Mail;

use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail as BaseOrderMail;

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
 * @property \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
 * @method static \App\Model\Mail\MailTemplate|null findMailTemplateForOrderStatus(\App\Model\Mail\MailTemplate[] $mailTemplates, \App\Model\Order\Status\OrderStatus $orderStatus)
 * @method __construct(\App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Twig\Environment $twig, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension, \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension, \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator)
 * @method static string getMailTemplateNameByStatus(\App\Model\Order\Status\OrderStatus $orderStatus)
 * @method array getVariablesReplacementsForBody(\App\Model\Order\Order $order)
 * @method string|null getTrackingInstructions(\App\Model\Order\Order $order)
 * @method string getBillingAddressHtmlTable(\App\Model\Order\Order $order)
 */
class OrderMail extends BaseOrderMail
{
}
