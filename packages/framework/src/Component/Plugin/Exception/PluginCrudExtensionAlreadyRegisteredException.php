<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Plugin\Exception;

use Exception;

class PluginCrudExtensionAlreadyRegisteredException extends Exception
{
    /**
     * @param string $type
     * @param string $key
     */
    public function __construct($type, $key, ?Exception $previous = null)
    {
        $message = sprintf('Plugin CRUD extension of type "%s" with key "%s" was already registered.', $type, $key);

        parent::__construct($message, 0, $previous);
    }
}
