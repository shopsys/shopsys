<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use LogicException;
use Throwable;

/**
 * The declaration of a datagrid cannot work. It is detected before any query exists, so the declaration fails
 * in development instead of the query failing for the administrator.
 */
final class InvalidDatagridDeclarationException extends LogicException
{
    public static function fieldNotSearchable(string $fieldName, string $reason, ?Throwable $previous = null): self
    {
        return new self(sprintf('Field "%s" cannot be searchable: %s', $fieldName, $reason), 0, $previous);
    }

    public static function fieldNotFilterable(string $fieldName, string $reason, ?Throwable $previous = null): self
    {
        return new self(sprintf('Field "%s" cannot be filterable: %s', $fieldName, $reason), 0, $previous);
    }

    public static function filterNotApplicable(string $filterName, string $reason, ?Throwable $previous = null): self
    {
        return new self(sprintf('Filter "%s" cannot be applied: %s', $filterName, $reason), 0, $previous);
    }
}
