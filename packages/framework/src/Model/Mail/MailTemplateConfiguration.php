<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail;
use Shopsys\FrameworkBundle\Model\Mail\Exception\InvalidMailTemplateVariablesConfigurationException;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail;

class MailTemplateConfiguration
{
    public const TYPE_ORDER_STATUS = 'order-status';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables[]
     */
    protected array $mailTemplateVariables = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(protected readonly OrderStatusFacade $orderStatusFacade)
    {
        $this->registerStaticMailTemplates();
        $this->registerOrderStatusMailTemplates();
    }

    /**
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    public function getMailTemplateVariablesBySlug(string $slug): MailTemplateVariables
    {
        if (!array_key_exists($slug, $this->mailTemplateVariables)) {
            throw new MailTemplateNotFoundException('Mail template not configured properly to be editable');
        }

        return $this->mailTemplateVariables[$slug];
    }

    /**
     * @return array
     */
    public function getReadableNamesIndexedBySlug(): array
    {
        return array_map(
            function (MailTemplateVariables $mailTemplateVariables) {
                return $mailTemplateVariables->getReadableName();
            },
            $this->mailTemplateVariables
        );
    }

    /**
     * @param string $mailTemplateSlug
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables $mailTemplateVariables
     */
    public function addMailTemplateVariables(string $mailTemplateSlug, MailTemplateVariables $mailTemplateVariables): void
    {
        if (array_key_exists($mailTemplateSlug, $this->mailTemplateVariables)) {
            throw new InvalidMailTemplateVariablesConfigurationException(
                sprintf('Template variables for mail template "%s" are already registered.', $mailTemplateSlug)
            );
        }

        $this->mailTemplateVariables[$mailTemplateSlug] = $mailTemplateVariables;
    }

    protected function registerOrderStatusMailTemplates(): void
    {
        $orderStatusMailTemplate = new MailTemplateVariables('', self::TYPE_ORDER_STATUS);

        $orderStatusMailTemplate
            ->addVariable(OrderMail::VARIABLE_NUMBER, t('Order number'))
            ->addVariable(OrderMail::VARIABLE_DATE, t('Date and time of order creation'))
            ->addVariable(OrderMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                OrderMail::VARIABLE_TRANSPORT,
                t('Chosen shipping name'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(OrderMail::VARIABLE_PAYMENT, t('Chosen payment name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                OrderMail::VARIABLE_TOTAL_PRICE,
                t('Total order price (including VAT)'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(
                OrderMail::VARIABLE_BILLING_ADDRESS,
                t('Billing address - name, last name, company, company number, tax number and billing address'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(
                OrderMail::VARIABLE_DELIVERY_ADDRESS,
                t('Delivery address'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(OrderMail::VARIABLE_NOTE, t('Note'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                OrderMail::VARIABLE_PRODUCTS,
                t('List of products in order (name, quantity, price per unit including VAT, total price per item including VAT)'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(
                OrderMail::VARIABLE_ORDER_DETAIL_URL,
                t('Order detail URL address'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(
                OrderMail::VARIABLE_TRANSPORT_INSTRUCTIONS,
                t('Shipping instructions'),
                MailTemplateVariables::CONTEXT_BODY
            )
            ->addVariable(
                OrderMail::VARIABLE_PAYMENT_INSTRUCTIONS,
                t('Payment instructions'),
                MailTemplateVariables::CONTEXT_BODY
            );

        $allOrderStatuses = $this->orderStatusFacade->getAll();

        foreach ($allOrderStatuses as $orderStatus) {
            $this->addMailTemplateVariables(
                OrderMail::getMailTemplateNameByStatus($orderStatus),
                $orderStatusMailTemplate->withNewName($orderStatus->getName())
            );
        }
    }

    protected function registerStaticMailTemplates(): void
    {
        // registration mail template
        $mailTemplate = new MailTemplateVariables(t('Registration confirmation'));
        $mailTemplate
            ->addVariable(RegistrationMail::VARIABLE_FIRST_NAME, t('First name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(RegistrationMail::VARIABLE_LAST_NAME, t('Last name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(RegistrationMail::VARIABLE_EMAIL, t('Email'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(RegistrationMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                RegistrationMail::VARIABLE_LOGIN_PAGE,
                t('Link to the log in page'),
                MailTemplateVariables::CONTEXT_BODY
            );
        $this->addMailTemplateVariables(MailTemplate::REGISTRATION_CONFIRM_NAME, $mailTemplate);

        // reset password mail template
        $mailTemplate = new MailTemplateVariables(t('Forgotten password sending'));
        $mailTemplate
            ->addVariable(ResetPasswordMail::VARIABLE_EMAIL, t('Email'))
            ->addVariable(
                ResetPasswordMail::VARIABLE_NEW_PASSWORD_URL,
                t('New password settings URL address'),
                MailTemplateVariables::CONTEXT_BOTH,
                MailTemplateVariables::REQUIRED_BODY
            );
        $this->addMailTemplateVariables(MailTemplate::RESET_PASSWORD_NAME, $mailTemplate);

        // personal data export mail template
        $mailTemplate = new MailTemplateVariables(t('Personal information export'));
        $mailTemplate
            ->addVariable(PersonalDataExportMail::VARIABLE_DOMAIN, t('E-shop name'))
            ->addVariable(PersonalDataExportMail::VARIABLE_EMAIL, t('Email'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                PersonalDataExportMail::VARIABLE_URL,
                t('E-shop URL address'),
                MailTemplateVariables::CONTEXT_BODY,
                MailTemplateVariables::REQUIRED_BODY
            );
        $this->addMailTemplateVariables(MailTemplate::PERSONAL_DATA_EXPORT_NAME, $mailTemplate);

        // personal data access mail template
        $mailTemplate = new MailTemplateVariables(t('Personal information overview'));
        $mailTemplate
            ->addVariable(PersonalDataAccessMail::VARIABLE_DOMAIN, t('E-shop name'))
            ->addVariable(PersonalDataAccessMail::VARIABLE_EMAIL, t('Email'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                PersonalDataAccessMail::VARIABLE_URL,
                t('E-shop URL address'),
                MailTemplateVariables::CONTEXT_BODY,
                MailTemplateVariables::REQUIRED_BODY
            );
        $this->addMailTemplateVariables(MailTemplate::PERSONAL_DATA_ACCESS_NAME, $mailTemplate);
    }
}
