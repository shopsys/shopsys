<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\ParameterFilterOption\Exception;

use RuntimeException;

class TypeNotImplementedException extends RuntimeException
{
    /**
     * @param string $parameterType
     */
    public function __construct(string $parameterType)
    {
        parent::__construct(sprintf(
            'Parameter filter option type "%s" has not been implemented yet.',
            $parameterType
        ));
    }
}
