<?php

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class SendMailFailedException extends Exception implements MailException
{
    /**
     * @var array
     */
    private $failedRecipients;

    public function __construct(array $failedRecipients, Exception $previous = null)
    {
        $this->failedRecipients = $failedRecipients;
        parent::__construct('Order mail was not send to ' . Debug::export($failedRecipients), 0, $previous);
    }

    public function getFailedRecipients()
    {
        return $this->failedRecipients;
    }
}
