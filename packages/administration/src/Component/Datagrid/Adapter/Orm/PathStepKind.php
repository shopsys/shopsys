<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * @internal this class is not intended to be used directly by other developers
 */
enum PathStepKind
{
    /**
     * The `translations` association addressed explicitly, joined with the current locale.
     */
    case TRANSLATIONS;

    /**
     * An association on the way, joined into the query.
     */
    case ASSOCIATION;

    /**
     * The terminal field of the entity the path arrived at.
     */
    case FIELD;

    /**
     * The terminal field found on the translation of the entity the path arrived at.
     */
    case TRANSLATED_FIELD;

    /**
     * The identifier of a to-one association, available without joining it.
     */
    case IDENTITY;
}
