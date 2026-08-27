<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;

class WithdrawalConfirmationMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly WithdrawalConfirmationMail $withdrawalConfirmationMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendMail(WithdrawalRequest $withdrawalRequest): void
    {
        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            WithdrawalConfirmationMail::ORDER_WITHDRAWAL_CONFIRMATION_NAME,
            $withdrawalRequest->getOrder()->getDomainId(),
        );

        $this->sendMailTemplate($mailTemplate, $withdrawalRequest);
    }

    public function sendMailTemplate(
        MailTemplate $mailTemplate,
        WithdrawalRequest $withdrawalRequest,
        ?string $forceSendTo = null,
    ): void {
        $messageData = $this->withdrawalConfirmationMail->createMessage($mailTemplate, $withdrawalRequest);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $withdrawalRequest->getOrder()->getDomainId());
    }
}
