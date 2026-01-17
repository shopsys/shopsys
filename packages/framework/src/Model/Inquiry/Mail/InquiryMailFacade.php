<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\Inquiry;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailTemplateNotFoundException;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class InquiryMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly InquiryMail $inquiryMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendMail(Inquiry $inquiry): void
    {
        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(InquiryMail::ADMIN_MAIL_TEMPLATE_NAME, $inquiry->getDomainId());
        $this->sendMailTemplate($mailTemplate, $inquiry);

        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME, $inquiry->getDomainId());
        $this->sendMailTemplate($mailTemplate, $inquiry);
    }

    public function sendMailTemplate(MailTemplate $mailTemplate, Inquiry $inquiry, ?string $forceSendTo = null): void
    {
        $messageData = match ($mailTemplate->getName()) {
            InquiryMail::ADMIN_MAIL_TEMPLATE_NAME => $this->inquiryMail->createMessageForAdmin($mailTemplate, $inquiry),
            InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME => $this->inquiryMail->createMessageForCustomer($mailTemplate, $inquiry),
            default => throw new MailTemplateNotFoundException($mailTemplate->getName()),
        };

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
