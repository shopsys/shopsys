<?php

namespace Shopsys\FrameworkBundle\Component\Plugin\Exception;

use Exception;

class UnknownPluginCrudExtensionTypeException extends Exception implements PluginException
{
    /**
     * @param string[] $knownTypes
     * @param \Exception|null $previous
     */
    public function __construct(string $unknownType, array $knownTypes, Exception $previous = null)
    {
        $message = sprintf(
            'Trying to register unknown type of plugin CRUD extension "%s". Known types are: %s.',
            $unknownType,
            implode(', ', $knownTypes)
        );

        parent::__construct($message, 0, $previous);
    }
}
