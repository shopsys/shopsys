<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Throwable;

class MailTemplateAlreadyExistsException extends Exception
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param \Throwable|null $previous
     */
    public function __construct(protected MailTemplate $mailTemplate, ?Throwable $previous = null)
    {
        $message = sprintf(
            'Mail template already exists (name=`%s`, domainId=`%s`, orderStatus=`%s`)',
            $mailTemplate->getName(),
            $mailTemplate->getDomainId(),
            $mailTemplate->getOrderStatus() === null ? 'null' : $mailTemplate->getOrderStatus()->getId(),
        );

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplate
     */
    public function getMailTemplate(): MailTemplate
    {
        return $this->mailTemplate;
    }
}
