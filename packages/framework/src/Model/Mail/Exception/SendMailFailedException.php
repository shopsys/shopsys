<?php

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendMailFailedException extends Exception implements MailException
{
    /**
     * @param \Symfony\Component\Mailer\Exception\TransportExceptionInterface $previous
     */
    public function __construct(TransportExceptionInterface $previous)
    {
        parent::__construct('There was a failure while sending emails', 0, $previous);
    }
}
