<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Throwable;

interface CrudDeleteHookExtensionInterface extends CrudHookableExtensionInterface
{
    public function beforeDelete(object $entity): void;

    public function afterDelete(object $entity): void;

    public function onDeleteError(object $entity, Throwable $exception): void;
}
