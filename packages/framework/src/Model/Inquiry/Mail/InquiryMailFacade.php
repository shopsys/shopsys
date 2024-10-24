<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\Inquiry;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class InquiryMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMail $inquiryMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly InquiryMail $inquiryMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry $inquiry
     */
    public function sendMail(Inquiry $inquiry): void
    {
        $mailTemplate = $this->mailTemplateFacade->get(InquiryMail::ADMIN_MAIL_TEMPLATE_NAME, $inquiry->getDomainId());
        $messageData = $this->inquiryMail->createMessageForAdmin($mailTemplate, $inquiry);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $inquiry->getDomainId());

        $mailTemplate = $this->mailTemplateFacade->get(InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME, $inquiry->getDomainId());
        $messageData = $this->inquiryMail->createMessageForCustomer($mailTemplate, $inquiry);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $inquiry->getDomainId());
    }
}
