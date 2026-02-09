<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class ResetPasswordMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ResetPasswordMail $resetPasswordMail,
        protected readonly Domain $domain,
    ) {
    }

    public function sendMail(Administrator $administrator): void
    {
        $domainId = $this->domain->getFirstDomainIdMatchingAdminSelectedLocale($administrator);

        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            ResetPasswordMail::MAIL_TEMPLATE_NAME,
            $domainId,
        );

        $this->sendMailTemplate($mailTemplate, $administrator);
    }

    public function sendMailTemplate(
        MailTemplate $mailTemplate,
        ResetPasswordInterface $administrator,
        ?string $forceSendTo = null,
    ): void {
        $messageData = $this->resetPasswordMail->createMessage($mailTemplate, $administrator);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
