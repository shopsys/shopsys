<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMail;
use Shopsys\FrameworkBundle\Model\Inquiry\Mail\InquiryMailFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

class InquiryMailTemplateSender implements MailTemplateSenderInterface
{
    public function __construct(
        protected readonly InquiryFacade $inquiryFacade,
        protected readonly InquiryMailFacade $inquiryMailFacade,
    ) {
    }

    #[Override]
    public function getFormLabelForEntityIdentifier(): string
    {
        return t('Inquiry ID');
    }

    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return in_array($mailTemplate->getName(), [InquiryMail::CUSTOMER_MAIL_TEMPLATE_NAME, InquiryMail::ADMIN_MAIL_TEMPLATE_NAME], true);
    }

    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $inquiry = $this->inquiryFacade->getById($entityId);
        $this->inquiryMailFacade->sendMailTemplate($mailTemplate, $inquiry, $mailTo);
    }
}
