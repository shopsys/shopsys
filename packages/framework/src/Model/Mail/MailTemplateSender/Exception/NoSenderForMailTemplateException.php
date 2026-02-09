<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;

class NoSenderForMailTemplateException extends Exception
{
    public function __construct(MailTemplate $mailTemplate)
    {
        parent::__construct(sprintf('No sender for mail template "%s" found.', $mailTemplate->getName()));
    }
}
