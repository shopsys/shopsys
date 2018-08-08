<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class EntityIdIsNotSetException extends Exception implements DataFixtureException
{
    public function __construct(string $referenceName, object $object, Exception $previous = null)
    {
        $message = 'Cannot create persistent reference "' . $referenceName . '" for entity without ID. '
            . 'Flush the entity ("' . get_class($object) . '") before creating a persistent reference.';

        parent::__construct($message, 0, $previous);
    }
}
