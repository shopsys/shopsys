<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

/**
 * Implemented by datagrid adapters that are backed by a known entity class.
 *
 * Features that need to work with the entity itself (e.g. drag-and-drop reordering, which persists
 * the new positions on the entity) depend on this, so they can obtain the entity class directly
 * from the adapter instead of relying on the CRUD definition.
 */
interface EntityClassAwareAdapterInterface extends AdapterInterface
{
    /**
     * @return class-string
     */
    public function getEntityClass(): string;
}
