<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class PersistentReferenceNotFoundException extends Exception
{
    public function __construct(string $referenceName, ?Exception $previous = null)
    {
        parent::__construct('Data fixture reference "' . $referenceName . '" not found', 0, $previous);
    }
}
