<?php

declare(strict_types=1);

namespace App\Model\Order\Mail;

use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade as BaseOrderMailFacade;

/**
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \App\Model\Order\Mail\OrderMail $orderMail
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer, \App\Model\Mail\MailTemplateFacade $mailTemplateFacade, \App\Model\Order\Mail\OrderMail $orderMail, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade)
 * @method sendEmail(\App\Model\Order\Order $order)
 * @method \App\Model\Mail\MailTemplate getMailTemplateByStatusAndDomainId(\App\Model\Order\Status\OrderStatus $orderStatus, int $domainId)
 */
class OrderMailFacade extends BaseOrderMailFacade
{
    /**
     * @param \App\Model\Order\Order $order
     */
    public function sendOrderStatusMailByOrder(Order $order): void
    {
        $mailTemplates = $this->mailTemplateFacade->getOrderStatusTemplatesByOrder($order);

        foreach ($mailTemplates as $mailTemplate) {
            $messageData = $this->orderMail->createMessage($mailTemplate, $order);
            $messageData->attachments = $this->uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
            $this->mailer->send($messageData);
        }
    }
}
