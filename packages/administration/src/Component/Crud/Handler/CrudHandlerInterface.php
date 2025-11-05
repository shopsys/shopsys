<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

/**
 * Marker interface indicating full CRUD support
 *
 * This interface serves as a semantic marker that indicates a handler is intended to support
 * all CRUD operations (Create, Read, Update, Delete).
 */
interface CrudHandlerInterface extends DeleteHandlerInterface
{
}
