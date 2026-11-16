<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\OrderedForm\Exception;

use Exception;

class OrderedFormException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct('Form `position`: ' . $message);
    }
}
