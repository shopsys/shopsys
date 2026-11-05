<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl;

enum DomainControlType
{
    /**
     * The datagrid displays no domain control and is not filtered by domain.
     */
    case NONE;

    /**
     * The datagrid displays its own domain filter with an "All domains" option,
     * remembering the selection under its own namespace.
     */
    case FILTER;

    /**
     * The datagrid follows the domain selected in the whole administration and always
     * works with exactly one domain. Use it for datagrids whose create and edit actions
     * are bound to the selected domain as well.
     */
    case SWITCHER;
}
