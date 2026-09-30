<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;

/**
 * Handler interface for edit operations
 *
 * This interface extends ReadHandlerInterface and adds edit functionality for CRUD controllers.
 */
interface EditHandlerInterface extends ReadHandlerInterface
{
    /**
     * Creates a data transfer object populated from the given entity
     *
     * @param \Shopsys\FrameworkBundle\Component\Utils\Presentable $entity The entity to create data from
     * @return object The populated data transfer object
     */
    public function createDataFromEntity(Presentable $entity): object;

    /**
     * Saves changes to the given entity using the provided data
     *
     * @param \Shopsys\FrameworkBundle\Component\Utils\Presentable $entity The entity to update
     * @param object $data The data transfer object with updated values
     */
    public function edit(Presentable $entity, object $data): void;
}
