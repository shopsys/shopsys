<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

/**
 * How many values an operation compares the field with, which decides the shape of the value it is given.
 */
class ExpressionValueArityEnum extends AbstractEnum
{
    /**
     * The operation compares nothing, the value is null — `isNull`.
     */
    public const string NONE = 'none';

    /**
     * The operation compares a single value — `equals`, `contains`, `greaterThan`.
     */
    public const string SINGLE = 'single';

    /**
     * The operation compares a list of values — `in`, `notIn`.
     */
    public const string LIST = 'list';

    /**
     * The operation compares a pair of bounds, given as a list of exactly two values — `between`.
     */
    public const string RANGE = 'range';
}
