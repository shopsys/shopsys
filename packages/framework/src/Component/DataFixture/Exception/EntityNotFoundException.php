<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class EntityNotFoundException extends Exception implements DataFixtureException
{
    public function __construct(string $referenceName, Exception $previous = null)
    {
        parent::__construct('Entity from reference  "' . $referenceName . '" not found.', 0, $previous);
    }
}
