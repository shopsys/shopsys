<?php

declare(strict_types=1);

namespace App\Model\Mail\Exception;

use App\Model\Mail\MailTemplate;

class MailTemplateAlreadyExistsException extends \Exception
{
    /**
     * @var \App\Model\Mail\MailTemplate
     */
    private MailTemplate $mailTemplate;

    /**
     * @param \App\Model\Mail\MailTemplate $mailTemplate
     * @param \Throwable|null $previous
     */
    public function __construct(MailTemplate $mailTemplate, ?\Throwable $previous = null)
    {
        $this->mailTemplate = $mailTemplate;
        $message = sprintf(
            'Mail template already exists (name=`%s`, domainId=`%s`, transportId=`%s`, paymentId=`%s`, orderStockStatus=`%s`)',
            $mailTemplate->getName(),
            $mailTemplate->getDomainId(),
            $mailTemplate->getTransport() === null ? "null" : $mailTemplate->getTransport()->getId(),
            $mailTemplate->getPayment() === null ? "null" : $mailTemplate->getPayment()->getId(),
            $mailTemplate->getOrderStockStatus()
        );

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return \App\Model\Mail\MailTemplate
     */
    public function getMailTemplate(): MailTemplate
    {
        return $this->mailTemplate;
    }
}
