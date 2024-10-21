<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Model\Administrator\Mail\TwoFactorAuthenticationMail;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMail;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerActivationMail;
use Shopsys\FrameworkBundle\Model\Customer\Mail\RegistrationMail;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMail;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMail;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMailTemplateVariablesProvider;
use Shopsys\FrameworkBundle\Model\Mail\Exception\InvalidMailTemplateVariablesConfigurationException;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail;

class MailTemplateConfiguration
{
    public const TYPE_ORDER_STATUS = 'order-status';
    public const TYPE_COMPLAINT_STATUS = 'complaint-status';
    public const TYPES_WITH_SEND_MAIL_SETTING = [
        self::TYPE_ORDER_STATUS,
        self::TYPE_COMPLAINT_STATUS,
    ];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables[]
     */
    protected array $mailTemplateVariables = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMailTemplateVariablesProvider $inquiryMailTemplateVariablesProvider
     */
    public function __construct(
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly InquiryMailTemplateVariablesProvider $inquiryMailTemplateVariablesProvider,
    ) {
        $this->registerStaticMailTemplates();
        $this->registerOrderStatusMailTemplates();
        $this->registerComplaintStatusMailTemplates();
        $this->registerTwoFactorAuthenticationCodeMailTemplate();
        $this->registerCustomerActivationMailTemplate();
        $this->registerInquiryMailTemplates();
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
            $this->mailTemplateVariables,
        );
    }

    /**
     * @param string $mailTemplateSlug
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables $mailTemplateVariables
     */
    public function addMailTemplateVariables(
        string $mailTemplateSlug,
        MailTemplateVariables $mailTemplateVariables,
    ): void {
        if (array_key_exists($mailTemplateSlug, $this->mailTemplateVariables)) {
            throw new InvalidMailTemplateVariablesConfigurationException(
                sprintf('Template variables for mail template "%s" are already registered.', $mailTemplateSlug),
            );
        }

        $this->mailTemplateVariables[$mailTemplateSlug] = $mailTemplateVariables;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createOrderStatusMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables('', self::TYPE_ORDER_STATUS);

        return $mailTemplateVariables
            ->addVariable(OrderMail::VARIABLE_NUMBER, t('Order number'))
            ->addVariable(OrderMail::VARIABLE_DATE, t('Date and time of order creation'))
            ->addVariable(OrderMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                OrderMail::VARIABLE_TRANSPORT,
                t('Chosen shipping name'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(OrderMail::VARIABLE_PAYMENT, t('Chosen payment name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                OrderMail::VARIABLE_TOTAL_PRICE,
                t('Total order price (including VAT)'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                OrderMail::VARIABLE_BILLING_ADDRESS,
                t('Billing address - name, last name, company, company number, tax number and billing address'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                OrderMail::VARIABLE_DELIVERY_ADDRESS,
                t('Delivery address'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(OrderMail::VARIABLE_NOTE, t('Note'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                OrderMail::VARIABLE_PRODUCTS,
                t('List of products in order (name, quantity, price per unit including VAT, total price per item including VAT)'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                OrderMail::VARIABLE_ORDER_DETAIL_URL,
                t('Order detail URL address'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                OrderMail::VARIABLE_TRANSPORT_INSTRUCTIONS,
                t('Shipping instructions'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                OrderMail::VARIABLE_PAYMENT_INSTRUCTIONS,
                t('Payment instructions'),
                MailTemplateVariables::CONTEXT_BODY,
            )
            ->addVariable(
                OrderMail::VARIABLE_TRACKING_INSTRUCTIONS,
                t('Tracking instructions'),
                MailTemplateVariables::CONTEXT_BODY,
            );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createComplaintStatusMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables('', self::TYPE_COMPLAINT_STATUS);

        return $mailTemplateVariables
            ->addVariable(ComplaintMail::VARIABLE_COMPLAINT_NUMBER, t('Complaint number'))
            ->addVariable(ComplaintMail::VARIABLE_COMPLAINT_DETAIL_URL, t('Complaint detail URL'))
            ->addVariable(ComplaintMail::VARIABLE_ORDER_NUMBER, t('Order number'))
            ->addVariable(ComplaintMail::VARIABLE_DATE, t('Date and time of order creation'))
            ->addVariable(ComplaintMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY);
    }

    protected function registerOrderStatusMailTemplates(): void
    {
        $mailTemplateVariables = $this->createOrderStatusMailTemplateVariables();

        $allOrderStatuses = $this->orderStatusFacade->getAll();

        foreach ($allOrderStatuses as $orderStatus) {
            $this->addMailTemplateVariables(
                OrderMail::getMailTemplateNameByStatus($orderStatus),
                $mailTemplateVariables->withNewName(t('Order') . ' - ' . $orderStatus->getName()),
            );
        }
    }

    protected function registerComplaintStatusMailTemplates(): void
    {
        $mailTemplateVariables = $this->createComplaintStatusMailTemplateVariables();

        $allComplaintStatuses = $this->complaintStatusFacade->getAll();

        foreach ($allComplaintStatuses as $complaintStatus) {
            $this->addMailTemplateVariables(
                ComplaintMail::getMailTemplateNameByStatus($complaintStatus),
                $mailTemplateVariables->withNewName(t('Complaint') . ' - ' . $complaintStatus->getName()),
            );
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createRegistrationConfirmationMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Registration confirmation'));

        return $mailTemplateVariables
            ->addVariable(RegistrationMail::VARIABLE_FIRST_NAME, t('First name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(RegistrationMail::VARIABLE_LAST_NAME, t('Last name'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(RegistrationMail::VARIABLE_EMAIL, t('Email'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(RegistrationMail::VARIABLE_URL, t('E-shop URL address'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                RegistrationMail::VARIABLE_LOGIN_PAGE,
                t('Link to the log in page'),
                MailTemplateVariables::CONTEXT_BODY,
            );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createResetPasswordMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Forgotten password sending'));

        return $mailTemplateVariables
            ->addVariable(ResetPasswordMail::VARIABLE_EMAIL, t('Email'))
            ->addVariable(
                ResetPasswordMail::VARIABLE_NEW_PASSWORD_URL,
                t('New password settings URL address'),
                MailTemplateVariables::CONTEXT_BOTH,
                MailTemplateVariables::REQUIRED_BODY,
            );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createPersonalDataExportMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Personal information export'));

        return $mailTemplateVariables
            ->addVariable(PersonalDataExportMail::VARIABLE_DOMAIN, t('E-shop name'))
            ->addVariable(PersonalDataExportMail::VARIABLE_EMAIL, t('Email'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                PersonalDataExportMail::VARIABLE_URL,
                t('E-shop URL address'),
                MailTemplateVariables::CONTEXT_BODY,
                MailTemplateVariables::REQUIRED_BODY,
            );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createPersonalDataAccessMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Personal information overview'));

        return $mailTemplateVariables
            ->addVariable(PersonalDataAccessMail::VARIABLE_DOMAIN, t('E-shop name'))
            ->addVariable(PersonalDataAccessMail::VARIABLE_EMAIL, t('Email'), MailTemplateVariables::CONTEXT_BODY)
            ->addVariable(
                PersonalDataAccessMail::VARIABLE_URL,
                t('E-shop URL address'),
                MailTemplateVariables::CONTEXT_BODY,
                MailTemplateVariables::REQUIRED_BODY,
            );
    }

    protected function registerStaticMailTemplates(): void
    {
        // registration mail template
        $mailTemplateVariables = $this->createRegistrationConfirmationMailTemplateVariables();
        $this->addMailTemplateVariables(MailTemplate::REGISTRATION_CONFIRM_NAME, $mailTemplateVariables);

        // reset password mail template
        $mailTemplateVariables = $this->createResetPasswordMailTemplateVariables();
        $this->addMailTemplateVariables(MailTemplate::RESET_PASSWORD_NAME, $mailTemplateVariables);

        // personal data export mail template
        $mailTemplateVariables = $this->createPersonalDataExportMailTemplateVariables();
        $this->addMailTemplateVariables(MailTemplate::PERSONAL_DATA_EXPORT_NAME, $mailTemplateVariables);

        // personal data access mail template
        $mailTemplateVariables = $this->createPersonalDataAccessMailTemplateVariables();
        $this->addMailTemplateVariables(MailTemplate::PERSONAL_DATA_ACCESS_NAME, $mailTemplateVariables);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createTwoFactorAuthenticationCodeMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Two factor authentication code'));

        return $mailTemplateVariables->addVariable(
            TwoFactorAuthenticationMail::VARIABLE_AUTHENTICATION_CODE,
            t('Authentication code'),
            MailTemplateVariables::CONTEXT_BODY,
            MailTemplateVariables::REQUIRED_BODY,
        );
    }

    protected function registerTwoFactorAuthenticationCodeMailTemplate(): void
    {
        $mailTemplateVariables = $this->createTwoFactorAuthenticationCodeMailTemplateVariables();

        $this->addMailTemplateVariables(TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE, $mailTemplateVariables);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
     */
    protected function createCustomerActivationMailTemplateVariables(): MailTemplateVariables
    {
        $mailTemplateVariables = new MailTemplateVariables(t('Customer activation'));

        return $mailTemplateVariables
            ->addVariable(CustomerActivationMail::VARIABLE_EMAIL, t('Email'))
            ->addVariable(CustomerActivationMail::VARIABLE_ACTIVATION_URL, t('Link to complete the registration'), MailTemplateVariables::CONTEXT_BODY, MailTemplateVariables::REQUIRED_BODY);
    }

    protected function registerCustomerActivationMailTemplate(): void
    {
        $mailTemplateVariables = $this->createCustomerActivationMailTemplateVariables();

        $this->addMailTemplateVariables(CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $mailTemplateVariables);
    }

    protected function registerInquiryMailTemplates(): void
    {
        $inquiryMailTemplateVariables = $this->inquiryMailTemplateVariablesProvider->create(InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME);
        $this->addMailTemplateVariables(InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME, $inquiryMailTemplateVariables);

        $inquiryMailTemplateVariables = $this->inquiryMailTemplateVariablesProvider->create(InquiryMail::ADMIN_MAIL_TEMPLATE_NAME);
        $this->addMailTemplateVariables(InquiryMail::ADMIN_MAIL_TEMPLATE_NAME, $inquiryMailTemplateVariables);
    }
}
