<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class PersistentReferenceNotFoundException extends Exception
{
    /**
     * @param string $referenceName
     */
    public function __construct($referenceName, ?Exception $previous = null)
    {
        parent::__construct('Data fixture reference "' . $referenceName . '" not found', 0, $previous);
    }
}
