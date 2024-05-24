<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Mail\Mailer;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class OrderMailFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail $orderMail
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly OrderMail $orderMail,
        protected readonly UploadedFileFacade $uploadedFileFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function sendEmail(Order $order)
    {
        $mailTemplate = $this->getMailTemplateByStatusAndDomainId($order->getStatus(), $order->getDomainId());

        if (!$mailTemplate->isSendMail()) {
            return;
        }

        $messageData = $this->orderMail->createMessage($mailTemplate, $order);
        $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
        $this->mailer->sendForDomain($messageData, $order->getDomainId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getMailTemplateByStatusAndDomainId(OrderStatus $orderStatus, $domainId)
    {
        $templateName = OrderMail::getMailTemplateNameByStatus($orderStatus);

        return $this->mailTemplateFacade->get($templateName, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function sendOrderStatusMailByOrder(Order $order): void
    {
        $mailTemplates = $this->mailTemplateFacade->getOrderStatusTemplatesByOrder($order);

        foreach ($mailTemplates as $mailTemplate) {
            $messageData = $this->orderMail->createMessage($mailTemplate, $order);
            $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
            $this->mailer->sendForDomain($messageData, $order->getDomainId());
        }
    }
}
