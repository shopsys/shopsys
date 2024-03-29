<?php

declare(strict_types=1);

namespace ShopsysNamespace;

/**
 * This file respects all checkers in this standard
 */
final class ValidClass
{
    private Type $parameterCamelCase;

    /**
     * @param \ShopsysNamespace\Type $parameterCamelCase
     */
    public function method(Type $parameterCamelCase)
    {
        $this->parameterCamelCase = $parameterCamelCase;

        return $this->parameterCamelCase;
    }
}
