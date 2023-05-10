<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Exception;

use Exception;

class DeprecatedParameterPropertyException extends Exception
{
    /**
     * @param string $parameterPropertyName
     */
    public function __construct(string $parameterPropertyName)
    {
        $message = sprintf('Deprecated %s property.', $parameterPropertyName);

        parent::__construct($message);
    }
}
