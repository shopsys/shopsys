<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;

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
     */
    public function create(object $data): Presentable;
}
