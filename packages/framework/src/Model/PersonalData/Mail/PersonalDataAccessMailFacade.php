<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;

class PersonalDataAccessMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly PersonalDataAccessMail $personalDataAccessMail,
        protected readonly PersonalDataExportMail $personalDataExportMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendMail(PersonalDataAccessRequest $personalDataAccessRequest): void
    {
        if ($personalDataAccessRequest->getType() === PersonalDataAccessRequest::TYPE_DISPLAY) {
            $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
                MailTemplate::PERSONAL_DATA_ACCESS_NAME,
                $personalDataAccessRequest->getDomainId(),
            );
        } else {
            $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
                MailTemplate::PERSONAL_DATA_EXPORT_NAME,
                $personalDataAccessRequest->getDomainId(),
            );
        }

        $this->sendMailTemplate($mailTemplate, $personalDataAccessRequest);
    }

    public function sendMailTemplate(
        MailTemplate $mailTemplate,
        PersonalDataAccessRequest $personalDataAccessRequest,
        ?string $forceSendTo = null,
    ): void {
        if ($mailTemplate->getName() === MailTemplate::PERSONAL_DATA_ACCESS_NAME) {
            $messageData = $this->personalDataAccessMail->createMessage($mailTemplate, $personalDataAccessRequest);
        } elseif ($mailTemplate->getName() === MailTemplate::PERSONAL_DATA_EXPORT_NAME) {
            $messageData = $this->personalDataExportMail->createMessage($mailTemplate, $personalDataAccessRequest);
        } else {
            throw new MailTemplateNotFoundException($mailTemplate->getName());
        }

        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $personalDataAccessRequest->getDomainId());
    }
}
