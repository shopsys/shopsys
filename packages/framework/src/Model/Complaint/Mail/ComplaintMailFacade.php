<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class ComplaintMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMail $complaintMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ComplaintMail $complaintMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     */
    public function sendEmail(Complaint $complaint): void
    {
        $order = $complaint->getOrder();

        $mailTemplate = $this->getMailTemplateByStatusAndDomainId($complaint->getStatus(), $order->getDomainId());

        if (!$mailTemplate->isSendMail()) {
            return;
        }

        $messageData = $this->complaintMail->createMessage($mailTemplate, $complaint);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $order->getDomainId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $complaintStatus
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getMailTemplateByStatusAndDomainId(ComplaintStatus $complaintStatus, int $domainId): MailTemplate
    {
        $templateName = ComplaintMail::getMailTemplateNameByStatus($complaintStatus);

        return $this->mailTemplateFacade->get($templateName, $domainId);
    }
}
