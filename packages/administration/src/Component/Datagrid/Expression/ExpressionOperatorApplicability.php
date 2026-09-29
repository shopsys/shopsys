<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * The rules saying which operations make sense on which path. They exist to prevent wrong answers, not to
 * enforce style: a text search on a date fails in the database, and a null test on a collection can never
 * match, because the query joins the related rows inwardly.
 *
 * A medium knowing its schema applies them before building; a declaration applies them before offering
 * an operation to the administrator.
 */
final class ExpressionOperatorApplicability
{
    /**
     * @var string[]
     */
    private const array TEXT_OPERATORS = [
        ExpressionOperatorEnum::CONTAINS,
        ExpressionOperatorEnum::STARTS_WITH,
        ExpressionOperatorEnum::ENDS_WITH,
    ];

    /**
     * @var string[]
     */
    private const array NULL_OPERATORS = [
        ExpressionOperatorEnum::IS_NULL,
        ExpressionOperatorEnum::IS_NOT_NULL,
    ];

    /**
     * @var string[]
     */
    private const array EMPTINESS_OPERATORS = [
        ExpressionOperatorEnum::IS_EMPTY,
        ExpressionOperatorEnum::IS_NOT_EMPTY,
    ];

    /**
     * @param string $operator One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     */
    public function assertApplicable(string $operator, PathDescription $pathDescription): void
    {
        $reason = $this->getReasonOfInapplicability($operator, $pathDescription);

        if ($reason !== null) {
            throw new OperatorNotApplicableToPathException($operator, $pathDescription, $reason);
        }
    }

    private function getReasonOfInapplicability(string $operator, PathDescription $pathDescription): ?string
    {
        if (in_array($operator, self::TEXT_OPERATORS, true) && $this->isText($pathDescription) === false) {
            return sprintf('text can only be searched in a text value, the path leads to %s.', $pathDescription->valueType->name);
        }

        if (in_array($operator, self::NULL_OPERATORS, true) && $pathDescription->isToMany()) {
            return 'a collection has no null to test, use isEmpty or isNotEmpty to test whether it has any related row.';
        }

        if (in_array($operator, self::EMPTINESS_OPERATORS, true) && $pathDescription->isToMany() === false) {
            return 'only a collection can be empty, use isNull or isNotNull to test a single value.';
        }

        return null;
    }

    /**
     * A value of an unknown type is not refused — nothing is known about it, so nothing is assumed either.
     */
    private function isText(PathDescription $pathDescription): bool
    {
        return in_array($pathDescription->valueType, [PathValueTypeEnum::STRING, PathValueTypeEnum::UNKNOWN], true);
    }
}
