<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

/**
 * Handler interface for delete operations
 *
 * This interface extends ReadHandlerInterface and adds delete functionality for CRUD controllers.
 */
interface DeleteHandlerInterface extends ReadHandlerInterface
{
    /**
     * Deletes the given entity
     *
     * The entity is already retrieved via getById() before this method is called.
     * All database operations within this method and its hooks are wrapped in a transaction.
     *
     * @param object $entity The entity to delete (complete object, not just ID)
     */
    public function delete(object $entity): void;
}
