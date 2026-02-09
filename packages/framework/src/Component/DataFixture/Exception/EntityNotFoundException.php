<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct(string $referenceName, ?Exception $previous = null)
    {
        parent::__construct('Entity from reference  "' . $referenceName . '" not found.', 0, $previous);
    }
}
