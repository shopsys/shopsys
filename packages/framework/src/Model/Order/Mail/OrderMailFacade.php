<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class OrderMailFacade
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly OrderMail $orderMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    public function sendEmail(Order $order, OrderStatus $orderStatus): void
    {
        $mailTemplate = $this->getMailTemplateByStatusAndDomainId($orderStatus, $order->getDomainId());

        if (!$mailTemplate->isSendMail()) {
            return;
        }

        $this->sendMailTemplate($mailTemplate, $order);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getMailTemplateByStatusAndDomainId(OrderStatus $orderStatus, $domainId)
    {
        $templateName = OrderMail::getMailTemplateNameByStatus($orderStatus);

        return $this->mailTemplateFacade->getWrappedWithGrapesJsBody($templateName, $domainId);
    }

    public function sendMailTemplate(MailTemplate $mailTemplate, Order $order, ?string $forceSendTo = null): void
    {
        $messageData = $this->orderMail->createMessage($mailTemplate, $order);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);

        if ($forceSendTo !== null) {
            $messageData->toEmail = $forceSendTo;
        }

        $this->mailer->sendForDomain($messageData, $order->getDomainId());
    }
}
