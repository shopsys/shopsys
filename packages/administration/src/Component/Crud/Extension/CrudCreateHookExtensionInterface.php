<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Throwable;

interface CrudCreateHookExtensionInterface extends CrudHookableExtensionInterface
{
    public function beforeCreate(object $data): void;

    public function afterCreate(Presentable $entity, object $data): void;

    public function onCreateError(object $data, Throwable $exception): void;
}
