<?php

namespace Shopsys\ShopBundle\Model\Gdpr\Mail;

use Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequest;
use Shopsys\ShopBundle\Model\Mail\MailerService;
use Shopsys\ShopBundle\Model\Mail\MailTemplate;
use Shopsys\ShopBundle\Model\Mail\MailTemplateFacade;

class GdprMailFacade
{
    /**
     * @var \Shopsys\ShopBundle\Model\Mail\MailerService
     */
    private $mailer;

    /**
     * @var \Shopsys\ShopBundle\Model\Mail\MailTemplateFacade
     */
    private $mailTemplateFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Gdpr\Mail\CredentialsRequestMail
     */
    private $credentialsRequestMail;

    /**
     * @param \Shopsys\ShopBundle\Model\Mail\MailerService $mailer
     * @param \Shopsys\ShopBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\ShopBundle\Model\Customer\Mail\ResetPasswordMail $resetPasswordMail
     */
    public function __construct(
        MailerService $mailer,
        MailTemplateFacade $mailTemplateFacade,
        CredentialsRequestMail $credentialsRequestMail
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->credentialsRequestMail = $credentialsRequestMail;
    }

    /**
     * @param PersonalDataAccessRequest $gdpr
     */
    public function sendMail(PersonalDataAccessRequest $gdpr)
    {
        $mailTemplate = $this->mailTemplateFacade->get(MailTemplate::PERSONAL_DATA_ACCESS_NAME, $gdpr->getDomainId());
        $messageData = $this->credentialsRequestMail->createMessage($mailTemplate, $gdpr);
        $messageData->attachmentsFilepaths = $this->mailTemplateFacade->getMailTemplateAttachmentsFilepaths($mailTemplate);
        $this->mailer->send($messageData);
    }
}
