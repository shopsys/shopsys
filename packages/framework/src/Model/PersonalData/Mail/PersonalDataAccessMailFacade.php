<?php

namespace Shopsys\FrameworkBundle\Model\PersonalData\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailerService;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;

class PersonalDataAccessMailFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailerService
     */
    protected $mailer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade
     */
    protected $mailTemplateFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail
     */
    protected $personalDataAccessMail;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail
     */
    protected $personalDataExportMail;

    public function __construct(
        MailerService $mailer,
        MailTemplateFacade $mailTemplateFacade,
        PersonalDataAccessMail $personalDataAccessMail,
        PersonalDataExportMail $personalDataExportMail
    ) {
        $this->mailer = $mailer;
        $this->mailTemplateFacade = $mailTemplateFacade;
        $this->personalDataAccessMail = $personalDataAccessMail;
        $this->personalDataExportMail = $personalDataExportMail;
    }

    public function sendMail(PersonalDataAccessRequest $personalDataAccessRequest)
    {
        if ($personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_DISPLAY) {
            $mailTemplate = $this->mailTemplateFacade->get(
                MailTemplate::PERSONAL_DATA_ACCESS_NAME,
                $personalDataAccessRequest->getDomainId()
            );

            $messageData = $this->personalDataAccessMail->createMessage($mailTemplate, $personalDataAccessRequest);
        } else {
            $mailTemplate = $this->mailTemplateFacade->get(
                MailTemplate::PERSONAL_DATA_EXPORT_NAME,
                $personalDataAccessRequest->getDomainId()
            );

            $messageData = $this->personalDataExportMail->createMessage($mailTemplate, $personalDataAccessRequest);
        }

        $messageData->attachmentsFilepaths = $this->mailTemplateFacade->getMailTemplateAttachmentsFilepaths($mailTemplate);

        $this->mailer->send($messageData);
    }
}
