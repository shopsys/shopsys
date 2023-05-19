<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;

class PersonalDataAccessMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataAccessMail $personalDataAccessMail
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\Mail\PersonalDataExportMail $personalDataExportMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly PersonalDataAccessMail $personalDataAccessMail,
        protected readonly PersonalDataExportMail $personalDataExportMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest $personalDataAccessRequest
     */
    public function sendMail(PersonalDataAccessRequest $personalDataAccessRequest)
    {
        if ($personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_DISPLAY) {
            $mailTemplate = $this->mailTemplateFacade->get(
                MailTemplate::PERSONAL_DATA_ACCESS_NAME,
                $personalDataAccessRequest->getDomainId(),
            );

            $messageData = $this->personalDataAccessMail->createMessage($mailTemplate, $personalDataAccessRequest);
        } else {
            $mailTemplate = $this->mailTemplateFacade->get(
                MailTemplate::PERSONAL_DATA_EXPORT_NAME,
                $personalDataAccessRequest->getDomainId(),
            );

            $messageData = $this->personalDataExportMail->createMessage($mailTemplate, $personalDataAccessRequest);
        }

        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        $this->mailer->sendForDomain($messageData, $personalDataAccessRequest->getDomainId());
    }
}
