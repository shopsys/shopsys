<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

/**
 * A condition nobody at hand is able to compile — a node outside the vocabulary the compiler was not
 * taught, or a medium-specific condition given the builder of another medium.
 */
class ConditionNotSupportedException extends Exception
{
    /**
     * @param class-string $conditionClass
     * @param class-string $consumerClass
     */
    public function __construct(string $conditionClass, string $consumerClass)
    {
        parent::__construct(sprintf(
            'Condition "%s" cannot be compiled by "%s". A condition outside the vocabulary needs a compiler that knows it, and a medium-specific condition works with the adapter of its medium only.',
            $conditionClass,
            $consumerClass,
        ));
    }
}
