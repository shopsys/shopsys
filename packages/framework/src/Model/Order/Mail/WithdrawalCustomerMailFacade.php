<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class WithdrawalCustomerMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\WithdrawalMail $withdrawalMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly WithdrawalMail $withdrawalMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function sendEmail(Order $order): void
    {
        $mailTemplate = $this->mailTemplateFacade->getWrappedWithGrapesJsBody(
            WithdrawalMail::MAIL_TEMPLATE_NAME,
            $order->getDomainId(),
        );

        if (!$mailTemplate->isSendMail()) {
            return;
        }

        $this->sendMailTemplate($mailTemplate, $order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string|null $forceSendTo
     */
    public function sendMailTemplate(MailTemplate $mailTemplate, Order $order, ?string $forceSendTo = null): void
    {
        $messageData = $this->withdrawalMail->createMessage($mailTemplate, $order);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $order->getDomainId());
    }
}
