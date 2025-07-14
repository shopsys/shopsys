<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerActivationMail;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender\Exception\NoSenderForMailTemplateException;
use Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFactory;

class CustomerMailTemplateSender implements MailTemplateSenderInterface
{
    protected const array SUPPORTED_MAIL_TEMPLATES = [
        MailTemplate::REGISTRATION_CONFIRM_NAME,
        MailTemplate::PERSONAL_DATA_ACCESS_NAME,
        MailTemplate::PERSONAL_DATA_EXPORT_NAME,
        MailTemplate::RESET_PASSWORD_NAME,
        CustomerActivationMail::CUSTOMER_ACTIVATION_NAME,
    ];

    protected const string PERSONAL_DATA_ACCESS_REQUEST_DUMMY_HASH = 'dummy-hash';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMailFacade $personalDataAccessMailFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFactory $personalDataAccessRequestFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade $resetPasswordMailFacade
     */
    public function __construct(
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerMailFacade $customerMailFacade,
        protected readonly PersonalDataAccessMailFacade $personalDataAccessMailFacade,
        protected readonly PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory,
        protected readonly PersonalDataAccessRequestFactory $personalDataAccessRequestFactory,
        protected readonly ResetPasswordMailFacade $resetPasswordMailFacade,
    ) {
    }

    /**
     * @return string
     */
    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Customer ID');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @return bool
     */
    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return in_array($mailTemplate->getName(), static::SUPPORTED_MAIL_TEMPLATES, true);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $mailTo
     * @param int|null $entityId
     */
    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById($entityId);

        match ($mailTemplate->getName()) {
            MailTemplate::REGISTRATION_CONFIRM_NAME => $this->customerMailFacade->sendRegistrationMailTemplate($mailTemplate, $customerUser, $mailTo),
            CustomerActivationMail::CUSTOMER_ACTIVATION_NAME => $this->sendCustomerActivationMail($mailTemplate, $customerUser, $mailTo),
            MailTemplate::PERSONAL_DATA_ACCESS_NAME => $this->sendPersonalDataAccessMail($customerUser, $mailTemplate, $mailTo),
            MailTemplate::PERSONAL_DATA_EXPORT_NAME => $this->sendPersonalDataExportMail($customerUser, $mailTemplate, $mailTo),
            MailTemplate::RESET_PASSWORD_NAME => $this->sendResetPasswordMail($mailTemplate, $customerUser, $mailTo),
            default => throw new NoSenderForMailTemplateException($mailTemplate),
        };
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $toEmail
     */
    protected function sendPersonalDataAccessMail(
        CustomerUser $customerUser,
        MailTemplate $mailTemplate,
        string $toEmail,
    ): void {
        $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForDisplay();
        $personalDataAccessRequestData->email = $customerUser->getEmail();
        $personalDataAccessRequestData->hash = static::PERSONAL_DATA_ACCESS_REQUEST_DUMMY_HASH;
        $personalDataAccessRequestData->domainId = $mailTemplate->getDomainId();

        $personalAccessRequest = $this->personalDataAccessRequestFactory->create(
            $personalDataAccessRequestData,
        );

        $this->personalDataAccessMailFacade->sendMailTemplate($mailTemplate, $personalAccessRequest, $toEmail);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $toEmail
     */
    protected function sendPersonalDataExportMail(
        CustomerUser $customerUser,
        MailTemplate $mailTemplate,
        string $toEmail,
    ): void {
        $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForExport();
        $personalDataAccessRequestData->email = $customerUser->getEmail();
        $personalDataAccessRequestData->hash = static::PERSONAL_DATA_ACCESS_REQUEST_DUMMY_HASH;
        $personalDataAccessRequestData->domainId = $mailTemplate->getDomainId();

        $personalAccessRequest = $this->personalDataAccessRequestFactory->create(
            $personalDataAccessRequestData,
        );

        $this->personalDataAccessMailFacade->sendMailTemplate($mailTemplate, $personalAccessRequest, $toEmail);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $toEmail
     */
    protected function sendCustomerActivationMail(
        MailTemplate $mailTemplate,
        CustomerUser $customerUser,
        string $toEmail,
    ): void {
        $resetPasswordUser = new DummyResetPasswordUser($customerUser->getEmail());
        $this->customerMailFacade->sendActivationMailTemplate($mailTemplate, $resetPasswordUser, $toEmail);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $toEmail
     */
    protected function sendResetPasswordMail(
        MailTemplate $mailTemplate,
        CustomerUser $customerUser,
        string $toEmail,
    ): void {
        $resetPasswordUser = new DummyResetPasswordUser($customerUser->getEmail());
        $this->resetPasswordMailFacade->sendMailTemplate($mailTemplate, $resetPasswordUser, $toEmail);
    }
}
