<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * @internal model of the ORM adapter, may change without upgrade notes — implement `PathWalkerInterface`, typically by decorating `PathWalker`, and change only the `PathDescription` of the walked path instead
 */
enum PathStepKind
{
    /**
     * The `translations` association addressed explicitly, joined with the current locale — `translations.name`.
     */
    case TRANSLATIONS;

    /**
     * An association on the way, joined into the query — `currency` of `currency.code`.
     */
    case ASSOCIATION;

    /**
     * The terminal field of the entity the path arrived at — `code` of `currency.code`.
     */
    case FIELD;

    /**
     * The terminal field found on the translation of the entity the path arrived at — `name` read as `o_tr.name`.
     */
    case TRANSLATED_FIELD;

    /**
     * The identifier of an owning to-one association, read without joining it — `currency.id` read as
     * `IDENTITY(o.currency)`.
     */
    case IDENTITY;
}
