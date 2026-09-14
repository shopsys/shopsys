<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Path;

/**
 * The kind of value a path leads to, independent of the medium storing it. It decides which operations make
 * sense on the path — text is searched, numbers and dates are compared, an association is tested for presence.
 */
enum PathValueTypeEnum
{
    case STRING;
    case INTEGER;
    case DECIMAL;
    case BOOLEAN;
    case DATE;
    case DATETIME;
    case MONEY;

    /**
     * The path ends with an association, so it leads to a related record rather than a value.
     */
    case ASSOCIATION;

    /**
     * The medium stores the value in a type this vocabulary does not know, typically a custom one. Nothing is
     * assumed about it, so no operation is refused on it.
     */
    case UNKNOWN;
}
