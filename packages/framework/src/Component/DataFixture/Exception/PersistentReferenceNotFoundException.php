<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class PersistentReferenceNotFoundException extends Exception implements DataFixtureException
{
    /**
     * @param string $referenceName
     * @param \Exception|null $previous
     */
    public function __construct($referenceName, Exception $previous = null)
    {
        parent::__construct('Data fixture reference "' . $referenceName . '" not found', 0, $previous);
    }
}
