<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Exception;

class MasterMailNotSetException extends Exception implements MailException
{
    public function __construct()
    {
        parent::__construct('Master mail is not set, please check MAILER_MASTER_EMAIL_ADDRESS env variable.');
    }
}
