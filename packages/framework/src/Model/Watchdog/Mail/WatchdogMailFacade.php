<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Watchdog\Watchdog;

class WatchdogMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly WatchdogMail $watchdogMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendMail(Watchdog $watchdog): void
    {
        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(WatchdogMail::WATCHDOG_MAIL_TEMPLATE_NAME, $watchdog->getDomainId());
        $messageData = $this->watchdogMail->createMessage($mailTemplate, $watchdog);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        $this->mailer->sendForDomain($messageData, $watchdog->getDomainId());
    }

    public function sendMailTemplate(MailTemplate $mailTemplate, Product $product, ?string $forceSendTo = null): void
    {
        $messageData = $this->watchdogMail->createMessageFromProductAndEmail(
            $mailTemplate,
            $forceSendTo,
            $product,
            $mailTemplate->getDomainId(),
        );

        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
