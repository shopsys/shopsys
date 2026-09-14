<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

/**
 * The operations an expression builder is able to express, and everything that is known about each of them
 * regardless of the medium — the shape of the value it compares (`getValueArity()`) and the paths it makes sense
 * on (`assertApplicable()`).
 *
 * The vocabulary is shared by every medium and every builder expresses every case in its `compare()`. A project
 * extends the vocabulary by a subclass of this enum adding the case together with its arity and applicability,
 * and by a builder of every medium it uses extending `AbstractExpressionBuilder` that expresses it.
 */
class ExpressionOperatorEnum extends AbstractEnum
{
    public const string EQUALS = 'equals';
    public const string NOT_EQUALS = 'notEquals';

    /**
     * Matches a text containing the given one, ignoring case. Escaping the wildcards of the medium is the
     * builder's job, so that a value the administrator typed is always taken literally.
     *
     * Applicable to a text path only — a medium knowing the type of the path refuses any other. The same
     * holds for `STARTS_WITH` and `ENDS_WITH`.
     */
    public const string CONTAINS = 'contains';
    public const string STARTS_WITH = 'startsWith';
    public const string ENDS_WITH = 'endsWith';
    public const string GREATER_THAN = 'greaterThan';
    public const string GREATER_THAN_OR_EQUAL = 'greaterThanOrEqual';
    public const string LESS_THAN = 'lessThan';
    public const string LESS_THAN_OR_EQUAL = 'lessThanOrEqual';

    /**
     * Matches a value within the given bounds `[from, to]`, both of them included.
     */
    public const string BETWEEN = 'between';

    /**
     * An empty list matches nothing.
     */
    public const string IN = 'in';

    /**
     * An empty list matches everything.
     */
    public const string NOT_IN = 'notIn';

    /**
     * Matches a record whose single value is missing. Over a path leading through a to-many association it is
     * refused — a collection has no null to test, use `IS_EMPTY` instead. The same holds for `IS_NOT_NULL`.
     */
    public const string IS_NULL = 'isNull';
    public const string IS_NOT_NULL = 'isNotNull';

    /**
     * Matches a record with no value on the given to-many path — no related row, or none holding the value:
     * "products without any image". Refused on any other path, use `IS_NULL` for a single value. The same holds
     * for `IS_NOT_EMPTY`.
     */
    public const string IS_EMPTY = 'isEmpty';
    public const string IS_NOT_EMPTY = 'isNotEmpty';

    /**
     * @var string[]
     */
    protected const array TEXT_OPERATORS = [
        self::CONTAINS,
        self::STARTS_WITH,
        self::ENDS_WITH,
    ];

    /**
     * @var string[]
     */
    protected const array NULL_OPERATORS = [
        self::IS_NULL,
        self::IS_NOT_NULL,
    ];

    /**
     * @var string[]
     */
    protected const array EMPTINESS_OPERATORS = [
        self::IS_EMPTY,
        self::IS_NOT_EMPTY,
    ];

    /**
     * @return string One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum
     */
    public function getValueArity(string $operator): string
    {
        $this->validateCase($operator);

        return match ($operator) {
            self::IS_NULL, self::IS_NOT_NULL, self::IS_EMPTY, self::IS_NOT_EMPTY => ExpressionValueArityEnum::NONE,
            self::IN, self::NOT_IN => ExpressionValueArityEnum::LIST,
            self::BETWEEN => ExpressionValueArityEnum::RANGE,
            default => ExpressionValueArityEnum::SINGLE,
        };
    }

    /**
     * The rules saying which operations make sense on which path. They exist to prevent wrong answers, not to
     * enforce style: a text search on a date fails in the database, and a null test on a collection would ask
     * about some related row, while the question meant is whether the collection has any.
     *
     * A medium knowing its schema applies them before building; a declaration applies them before offering
     * an operation to the administrator.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException
     */
    public function assertApplicable(string $operator, PathDescription $pathDescription): void
    {
        $reason = $this->getReasonOfInapplicability($operator, $pathDescription);

        if ($reason !== null) {
            throw new OperatorNotApplicableToPathException($operator, $pathDescription, $reason);
        }
    }

    protected function getReasonOfInapplicability(string $operator, PathDescription $pathDescription): ?string
    {
        if (in_array($operator, static::TEXT_OPERATORS, true) && $this->isText($pathDescription) === false) {
            return sprintf('text can only be searched in a text value, the path leads to %s.', $pathDescription->valueType->name);
        }

        if (in_array($operator, static::NULL_OPERATORS, true) && $pathDescription->isToMany()) {
            return 'a collection has no null to test, use isEmpty or isNotEmpty to test whether it has any related row.';
        }

        if (in_array($operator, static::EMPTINESS_OPERATORS, true) && $pathDescription->isToMany() === false) {
            return 'only a collection can be empty, use isNull or isNotNull to test a single value.';
        }

        return null;
    }

    /**
     * Only a value known to be text is searched — a database refuses a text function on a value of another type,
     * and a value of an unknown type (a GUID, a JSON document) may well be one.
     */
    protected function isText(PathDescription $pathDescription): bool
    {
        return $pathDescription->valueType === PathValueTypeEnum::STRING;
    }
}
