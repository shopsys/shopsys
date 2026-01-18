<?php

declare(strict_types=1);

namespace App\Model\Order\Mail;

use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade as BaseOrderMailFacade;

/**
 * @property \App\Model\Mail\MailTemplateFacade $mailTemplateFacade
 * @property \App\Model\Order\Mail\OrderMail $orderMail
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer, \App\Model\Mail\MailTemplateFacade $mailTemplateFacade, \App\Model\Order\Mail\OrderMail $orderMail, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade)
 * @method void sendEmail(\App\Model\Order\Order $order, \App\Model\Order\Status\OrderStatus $orderStatus)
 * @method \App\Model\Mail\MailTemplate getMailTemplateByStatusAndDomainId(\App\Model\Order\Status\OrderStatus $orderStatus, int $domainId)
 * @method void sendMailTemplate(\App\Model\Mail\MailTemplate $mailTemplate, \App\Model\Order\Order $order, string|null $forceSendTo = null)
 */
class OrderMailFacade extends BaseOrderMailFacade
{
}
