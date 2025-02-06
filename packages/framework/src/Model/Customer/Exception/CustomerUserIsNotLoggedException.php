<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class CustomerUserIsNotLoggedException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'No customer user is currently logged-in even though should be.')
    {
        parent::__construct($message);
    }
}
