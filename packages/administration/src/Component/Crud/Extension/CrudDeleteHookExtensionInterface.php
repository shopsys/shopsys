<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Throwable;

interface CrudDeleteHookExtensionInterface extends CrudHookableExtensionInterface
{
    public function beforeDelete(Presentable $entity): void;

    public function afterDelete(Presentable $entity): void;

    public function onDeleteError(Presentable $entity, Throwable $exception): void;
}
