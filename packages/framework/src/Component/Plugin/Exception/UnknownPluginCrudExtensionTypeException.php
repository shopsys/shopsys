<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Plugin\Exception;

use Exception;

class UnknownPluginCrudExtensionTypeException extends Exception
{
    /**
     * @param string[] $knownTypes
     */
    public function __construct(string $unknownType, array $knownTypes, ?Exception $previous = null)
    {
        $message = sprintf(
            'Trying to register unknown type of plugin CRUD extension "%s". Known types are: %s.',
            $unknownType,
            implode(', ', $knownTypes),
        );

        parent::__construct($message, 0, $previous);
    }
}
