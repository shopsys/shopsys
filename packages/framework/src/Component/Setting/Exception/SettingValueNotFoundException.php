<?php

namespace Shopsys\FrameworkBundle\Component\Setting\Exception;

use Exception;

class SettingValueNotFoundException extends Exception implements SettingException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
