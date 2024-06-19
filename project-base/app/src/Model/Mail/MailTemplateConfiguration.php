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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    private function createExtendedOrderStatusMailTemplatesVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Order status changed'));

        return $mailTemplateVariables
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
    }

    private function registerExtendedOrderStatusMailTemplates(): void
    {
        $mailTemplateVariables = $this->createExtendedOrderStatusMailTemplatesVariables();

        $this->addMailTemplateVariables(MailTemplate::ORDER_STATUS_NAME, $mailTemplateVariables);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    private function createCustomerActivationMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Customer activation'));

        return $mailTemplateVariables
            ->addVariable(CustomerActivationMail::VARIABLE_EMAIL, t('Email'))
            ->addVariable(CustomerActivationMail::VARIABLE_ACTIVATION_URL, t('Link to complete the registration'), MailTemplateVariables::CONTEXT_BODY, MailTemplateVariables::REQUIRED_BODY);
    }

    private function registerCustomerActivationMailTemplate(): void
    {
        $mailTemplateVariables = $this->createCustomerActivationMailTemplateVariables();

        $this->addMailTemplateVariables(CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $mailTemplateVariables);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createOrderStatusMailTemplateVariables(): MailTemplateVariables
    {
        return parent::createOrderStatusMailTemplateVariables()
            ->addVariable(OrderMail::VARIABLE_TRACKING_INSTRUCTIONS, t('Tracking instructions'), MailTemplateVariables::CONTEXT_BODY);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    private function createTwoFactorAuthenticationCodeMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Two factor authentication code'));

        return $mailTemplateVariables->addVariable(
            TwoFactorAuthenticationMail::VARIABLE_AUTHENTICATION_CODE,
            t('Authentication code'),
            MailTemplateVariables::CONTEXT_BODY,
            MailTemplateVariables::REQUIRED_BODY,
        );
    }

    private function registerTwoFactorAuthenticationCodeMailTemplate(): void
    {
        $mailTemplateVariables = $this->createTwoFactorAuthenticationCodeMailTemplateVariables();

        $this->addMailTemplateVariables(TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE, $mailTemplateVariables);
    }
}
