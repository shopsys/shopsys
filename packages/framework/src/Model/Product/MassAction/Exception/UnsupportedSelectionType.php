<?php

namespace Shopsys\FrameworkBundle\Model\Product\MassAction\Exception;

use Exception;

class UnsupportedSelectionType extends Exception implements MassActionException
{
    public function __construct(string $selectionType, Exception $previous = null)
    {
        parent::__construct(sprintf('Selection type "%s" is not supported', $selectionType), 0, $previous);
    }
}
