<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity\Exception;

use Exception;

class CurrentAdministratorActivityNotFoundException extends Exception implements AdministratorActivityException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
