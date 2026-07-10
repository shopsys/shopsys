<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;

/**
 * Handler interface for read operations
 *
 * This interface is used by CRUD controllers for actions that need to retrieve entities by ID.
 */
interface ReadHandlerInterface extends HandlerInterface
{
    /**
     * Retrieves an entity by its ID
     *
     * @param int $id Entity identifier
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException When entity is not found
     */
    public function getById(int $id): Presentable;
}
