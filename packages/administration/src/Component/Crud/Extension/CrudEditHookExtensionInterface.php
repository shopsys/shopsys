<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Throwable;

interface CrudEditHookExtensionInterface extends CrudHookableExtensionInterface
{
    public function beforeEdit(object $entity, object $data): void;

    public function afterEdit(object $entity, object $data): void;

    public function onEditError(object $entity, object $data, Throwable $exception): void;
}
