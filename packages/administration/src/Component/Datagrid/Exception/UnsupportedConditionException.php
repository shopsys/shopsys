<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use LogicException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * An adapter or its expression builder cannot express or evaluate what it is asked for. It is a mistake in the code,
 * so it fails right away instead of giving a wrong answer.
 */
final class UnsupportedConditionException extends LogicException
{
    /**
     * @param class-string $conditionClass
     * @param class-string $builderClass
     */
    public static function conditionNotSupported(string $conditionClass, string $builderClass): self
    {
        return new self(sprintf(
            'Condition "%s" cannot be compiled by "%s". A condition outside the vocabulary works only with the adapter whose builder knows it.',
            $conditionClass,
            $builderClass,
        ));
    }

    /**
     * @param class-string $builderClass
     */
    public static function operatorNotSupported(string $operator, string $builderClass): self
    {
        return new self(sprintf(
            'Operator "%s" is not expressed by the "%s" expression builder, extend the builder to express it.',
            $operator,
            $builderClass,
        ));
    }

    /**
     * The operation would give a wrong answer on the path (see `ExpressionOperatorEnum::findReasonOfInapplicability()`).
     */
    public static function operatorNotApplicableToPath(
        string $operator,
        PathDescription $pathDescription,
        string $reason,
    ): self {
        return new self(sprintf(
            'Operator "%s" cannot be applied to path "%s": %s',
            $operator,
            $pathDescription->path,
            $reason,
        ));
    }

    /**
     * The operation makes no sense on a value of a record — a text search on a date.
     * An adapter without a schema finds that out only when it evaluates the value.
     */
    public static function operatorNotApplicableToValue(string $operator, mixed $value): self
    {
        return new self(sprintf(
            'Operator "%s" cannot be applied to a value of type "%s".',
            $operator,
            get_debug_type($value),
        ));
    }

    /**
     * @param string[] $availableKeys
     */
    public static function pathNotFoundInRow(string $path, string $missingSegment, array $availableKeys): self
    {
        return new self(sprintf(
            'Path "%s" cannot be read, there is no "%s" in the row. Available: "%s".',
            $path,
            $missingSegment,
            implode('", "', $availableKeys),
        ));
    }

    public static function toManySelectNotSupported(string $path): self
    {
        return new self(sprintf(
            'Path "%s" leads through a to-many association and cannot be selected, because joining it would list an entity once per related row. Use a virtual field with a transform instead.',
            $path,
        ));
    }
}
