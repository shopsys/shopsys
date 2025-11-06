<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Throwable;

interface CrudDeleteHookExtensionInterface extends CrudHookableExtensionInterface
{
    /**
     * @param object $entity
     */
    public function beforeDelete(object $entity): void;

    /**
     * @param object $entity
     */
    public function afterDelete(object $entity): void;

    /**
     * @param object $entity
     * @param \Throwable $exception
     */
    public function onDeleteError(object $entity, Throwable $exception): void;
}
