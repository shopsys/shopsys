<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMail;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMailFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

class ComplaintMailTemplateSender implements MailTemplateSenderInterface
{
    public function __construct(
        protected readonly ComplaintFacade $complaintFacade,
        protected readonly ComplaintMailFacade $complaintMailFacade,
    ) {
    }

    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Complaint ID');
    }

    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return str_contains($mailTemplate->getName(), ComplaintMail::MAIL_TEMPLATE_NAME_PREFIX);
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $complaint = $this->complaintFacade->getById($entityId);
        $this->complaintMailFacade->sendMailTemplate($mailTemplate, $complaint, $mailTo);
    }
}
