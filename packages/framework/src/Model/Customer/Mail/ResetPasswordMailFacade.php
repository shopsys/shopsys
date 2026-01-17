<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Mail;

use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class ResetPasswordMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ResetPasswordMail $resetPasswordMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendMail(CustomerUser $customerUser)
    {
        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            MailTemplate::RESET_PASSWORD_NAME,
            $customerUser->getDomainId(),
        );
        $this->sendMailTemplate($mailTemplate, $customerUser);
    }

    public function sendMailTemplate(
        MailTemplate $mailTemplate,
        ResetPasswordInterface $customerUser,
        ?string $forceSendTo = null,
    ): void {
        $messageData = $this->resetPasswordMail->createMessage($mailTemplate, $customerUser);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $mailTemplate->getDomainId());
    }
}
