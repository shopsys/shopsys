<?php

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;
use Throwable;

class AccessingNotLoadedProperty extends Exception implements ProductException
{
    public function __construct(string $propertyName, string $twigFunctionName, int $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            'You are trying to access "%s" but the property is not loaded. 
            You should access the property solely via Twig function "%s."',
            $propertyName,
            $twigFunctionName
        );
        parent::__construct($message, $code, $previous);
    }
}
