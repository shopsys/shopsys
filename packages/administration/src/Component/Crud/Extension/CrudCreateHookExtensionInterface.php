<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Throwable;

interface CrudCreateHookExtensionInterface extends CrudHookableExtensionInterface
{
    public function beforeCreate(object $data): void;

    public function afterCreate(object $entity, object $data): void;

    public function onCreateError(object $data, Throwable $exception): void;
}
