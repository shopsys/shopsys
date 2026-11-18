<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

/**
 * The operations of the condition vocabulary and what is known about each of them regardless of the builder:
 * the shape of its value (`getValueArity()`), the paths it applies to (`findReasonOfInapplicability()`) and its
 * label (`getLabel()`).
 *
 * Every adapter evaluates the operations with the same meaning:
 *
 * - `contains`, `startsWith` and `endsWith` match text literally (a builder escapes its own wildcards),
 *   regardless of case and diacritics — "Hrníček" contains "hrnicek"; the other operations compare text exactly;
 * - a missing value (null) matches no comparison but `isNull`, so it matches every negation — `notEquals`,
 *   `notIn` and `not()`;
 * - over a path through a to-many association, a comparison matches when some related row matches.
 *
 * A project adds an operation by a subclass of this enum, giving the operation its arity, applicability and
 * label, and by extending every builder it uses (see `AbstractExpressionBuilder::compare()`).
 */
class ExpressionOperatorEnum extends AbstractEnum
{
    public const string EQUALS = 'equals';
    public const string NOT_EQUALS = 'notEquals';
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

    public function getValueArity(string $operator): ExpressionValueArityEnum
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
     * Fails when the operation would give a wrong answer on the path (see `findReasonOfInapplicability()`).
     */
    public function assertApplicable(string $operator, PathDescription $pathDescription): void
    {
        $reason = $this->findReasonOfInapplicability($operator, $pathDescription);

        if ($reason !== null) {
            throw UnsupportedConditionException::operatorNotApplicableToPath($operator, $pathDescription, $reason);
        }
    }

    /**
     * Why the operation would give a wrong answer on the path, null when it is applicable:
     *
     * - a text search on a value other than text fails in the database;
     * - a null test on a collection asks about some related row, not whether the collection has any;
     * - an emptiness test on a single value has no collection to test.
     */
    public function findReasonOfInapplicability(string $operator, PathDescription $pathDescription): ?string
    {
        if (in_array($operator, static::TEXT_OPERATORS, true) && $this->isText($pathDescription) === false) {
            return sprintf('text can only be searched in a text value, the path leads to %s.', $pathDescription->valueType->name);
        }

        if (in_array($operator, static::NULL_OPERATORS, true) && $pathDescription->isToMany()) {
            return 'a collection has no null to test, use isEmpty or isNotEmpty to test whether it has any related row.';
        }

        if (in_array($operator, static::EMPTINESS_OPERATORS, true) && $pathDescription->isToOne()) {
            return 'only a collection can be empty, use isNull or isNotNull to test a single value.';
        }

        return null;
    }

    /**
     * How the operation reads in the filter form.
     */
    public function getLabel(string $operator): string
    {
        return $this->getLabels()[$operator] ?? $operator;
    }

    /**
     * Only a value known to be text is searched — a value of an unknown type may be a JSON document.
     */
    protected function isText(PathDescription $pathDescription): bool
    {
        return $pathDescription->valueType === PathValueTypeEnum::STRING;
    }

    /**
     * @return array<string, string>
     */
    protected function getLabels(): array
    {
        return [
            self::EQUALS => t('is'),
            self::NOT_EQUALS => t('is not'),
            self::CONTAINS => t('contains'),
            self::STARTS_WITH => t('starts with'),
            self::ENDS_WITH => t('ends with'),
            self::GREATER_THAN => t('is greater than'),
            self::GREATER_THAN_OR_EQUAL => t('is greater or equal to'),
            self::LESS_THAN => t('is less than'),
            self::LESS_THAN_OR_EQUAL => t('is less or equal to'),
            self::BETWEEN => t('is between'),
            self::IN => t('is one of'),
            self::NOT_IN => t('is none of'),
            self::IS_NULL => t('is not filled'),
            self::IS_NOT_NULL => t('is filled'),
            self::IS_EMPTY => t('has none'),
            self::IS_NOT_EMPTY => t('has some'),
        ];
    }
}
