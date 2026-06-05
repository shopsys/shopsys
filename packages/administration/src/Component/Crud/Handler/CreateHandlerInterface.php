<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

/**
 * Handler interface for create operations
 *
 * This interface extends ReadHandlerInterface and adds create functionality for CRUD controllers.
 */
interface CreateHandlerInterface extends ReadHandlerInterface
{
    /**
     * Creates an empty data transfer object with default values
     *
     * @return object The new data transfer object
     */
    public function createData(): object;

    /**
     * Creates a new entity from the provided data
     *
     * @param object $data The data transfer object with values for the new entity
     * @return \Shopsys\FrameworkBundle\Component\Utils\Presentable The newly created entity
     */
    public function create(object $data): object;
}
