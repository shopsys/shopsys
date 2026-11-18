<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

/**
 * How many values an operation compares the field with, which decides the shape of the value it is given.
 * The value names the arity in the filter form and in its `data-arity` attribute.
 */
enum ExpressionValueArityEnum: string
{
    /**
     * The operation compares nothing, the value is null — `isNull`.
     */
    case NONE = 'none';

    /**
     * The operation compares a single value — `equals`, `contains`, `greaterThan`.
     */
    case SINGLE = 'single';

    /**
     * The operation compares a list of values — `in`, `notIn`.
     */
    case LIST = 'list';

    /**
     * The operation compares a pair of bounds, given as a list of exactly two values — `between`.
     */
    case RANGE = 'range';
}
