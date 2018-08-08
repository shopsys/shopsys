<?php

namespace Shopsys\FrameworkBundle\Component\Plugin\Exception;

use Exception;

class PluginCrudExtensionAlreadyRegisteredException extends Exception implements PluginException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $type, string $key, Exception $previous = null)
    {
        $message = sprintf('Plugin CRUD extension of type "%s" with key "%s" was already registered.', $type, $key);

        parent::__construct($message, 0, $previous);
    }
}
