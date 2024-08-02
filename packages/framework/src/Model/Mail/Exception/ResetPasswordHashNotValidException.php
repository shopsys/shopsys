<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Exception;

class ResetPasswordHashNotValidException extends Exception implements MailException
{
}
