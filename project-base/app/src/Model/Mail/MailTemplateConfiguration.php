<?php

declare(strict_types=1);

namespace App\Model\Mail;

use App\Model\Administrator\Mail\TwoFactorAuthenticationMail;
use App\Model\Customer\Mail\CustomerActivationMail;
use App\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration as BaseMailTemplateConfiguration;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class MailTemplateConfiguration extends BaseMailTemplateConfiguration
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(OrderStatusFacade $orderStatusFacade)
    {
        parent::__construct($orderStatusFacade);

        $this->registerExtendedOrderStatusMailTemplates();
        $this->registerCustomerActivationMailTemplate();
        $this->registerTwoFactorAuthenticationCodeMailTemplate();
    }

    private function registerExtendedOrderStatusMailTemplates(): void
    {
        $mailTemplate = new MailTemplateVariables(t('Změna stavu objednávky'));
        $mailTemplate
            ->addVariable(OrderMail::VARIABLE_NUMBER, t('Order number'))
            ->addVariable(OrderMail::VARIABLE_DATE, t('Date and time of order creation'))
            ->addVariable(OrderMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TRANSPORT, t('Chosen shipping name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_PAYMENT, t('Chosen payment name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TOTAL_PRICE, t('Total order price (including VAT)'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_BILLING_ADDRESS, t('Billing address - name, last name, company, company number, tax number and billing address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_DELIVERY_ADDRESS, t('Delivery address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_NOTE, t('Note'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_PRODUCTS, t('List of products in order (name, quantity, price per unit including VAT, total price per item including VAT)'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_ORDER_DETAIL_URL, t('Order detail URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TRANSPORT_INSTRUCTIONS, t('Shipping instructions'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_PAYMENT_INSTRUCTIONS, t('Payment instructions'), MailTemplateVariables::CONTEXT_BODY);

        $this->addMailTemplateVariables(MailTemplate::ORDER_STATUS_NAME, $mailTemplate);
    }

    private function registerCustomerActivationMailTemplate(): void
    {
        $mailTemplate = new MailTemplateVariables(t('Dokončení registrace'));
        $mailTemplate
            ->addVariable(CustomerActivationMail::VARIABLE_EMAIL, t('Email'))
            ->addVariable(CustomerActivationMail::VARIABLE_ACTIVATION_URL, t('Odkaz na dokončení registrace'), MailTemplateVariables::CONTEXT_BODY, MailTemplateVariables::REQUIRED_BODY);
        $this->addMailTemplateVariables(CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $mailTemplate);
    }

    protected function registerOrderStatusMailTemplates(): void
    {
        $orderStatusMailTemplate = new MailTemplateVariables('', self::TYPE_ORDER_STATUS);

        $orderStatusMailTemplate
            ->addVariable(OrderMail::VARIABLE_NUMBER, t('Order number'))
            ->addVariable(OrderMail::VARIABLE_DATE, t('Date and time of order creation'))
            ->addVariable(OrderMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TRANSPORT, t('Chosen shipping name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_PAYMENT, t('Chosen payment name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TOTAL_PRICE, t('Total order price (including VAT)'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_BILLING_ADDRESS, t('Billing address - name, last name, company, company number, tax number and billing address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_DELIVERY_ADDRESS, t('Delivery address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_NOTE, t('Note'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_PRODUCTS, t('List of products in order (name, quantity, price per unit including VAT, total price per item including VAT)'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_ORDER_DETAIL_URL, t('Order detail URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TRANSPORT_INSTRUCTIONS, t('Shipping instructions'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_PAYMENT_INSTRUCTIONS, t('Payment instructions'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(OrderMail::VARIABLE_TRACKING_INSTRUCTIONS, t('Tracking instructions'), MailTemplateVariables::CONTEXT_BODY);

        /** @var \App\Model\Order\Status\OrderStatus[] $allOrderStatuses */
        $allOrderStatuses = $this->orderStatusFacade->getAll();

        foreach ($allOrderStatuses as $orderStatus) {
            $this->addMailTemplateVariables(
                OrderMail::getMailTemplateNameByStatus($orderStatus),
                $orderStatusMailTemplate->withNewName($orderStatus->getName()),
            );
        }
    }

    private function registerTwoFactorAuthenticationCodeMailTemplate(): void
    {
        $mailTemplate = new MailTemplateVariables(t('Two factor authentication code'));
        $mailTemplate->addVariable(
            TwoFactorAuthenticationMail::VARIABLE_AUTHENTICATION_CODE,
            t('Authentication code'),
            MailTemplateVariables::CONTEXT_BODY,
            MailTemplateVariables::REQUIRED_BODY,
        );

        $this->addMailTemplateVariables(TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE, $mailTemplate);
    }
}
