<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailDisplayPriceResolver;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Twig\Environment;

class WithdrawalMail implements MessageFactoryInterface
{
    public const string MAIL_TEMPLATE_NAME = 'withdrawal';

    public const string VARIABLE_NUMBER = '{number}';
    public const string VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const string VARIABLE_PRODUCTS = '{products}';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Twig\Environment $twig
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailDisplayPriceResolver $mailDisplayPriceResolver
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly Environment $twig,
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly Domain $domain,
        protected readonly MailDisplayPriceResolver $mailDisplayPriceResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    #[Override]
    public function createMessage(MailTemplate $mailTemplate, $order): MessageData
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return array<string, string>
     */
    protected function getVariablesReplacementsForBody(Order $order): array
    {
        return [
            self::VARIABLE_NUMBER => htmlspecialchars($order->getNumber(), ENT_QUOTES),
            self::VARIABLE_ORDER_DETAIL_URL => $this->orderUrlGenerator->getOrderDetailUrl($order),
            self::VARIABLE_PRODUCTS => $this->getProductsHtmlTable($order),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return array<string, string>
     */
    protected function getVariablesReplacementsForSubject(Order $order): array
    {
        return [
            self::VARIABLE_NUMBER => htmlspecialchars($order->getNumber(), ENT_QUOTES),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return string
     */
    protected function getProductsHtmlTable(Order $order): string
    {
        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById(
            $order->getItems(),
        );

        return $this->twig->render('@ShopsysFramework/Mail/Order/products.html.twig', [
            'order' => $order,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
            'orderLocale' => $this->domain->getDomainConfigById($order->getDomainId())->getLocale(),
            'displayPrice' => $this->mailDisplayPriceResolver->getDisplayPrice(),
        ]);
    }
}
