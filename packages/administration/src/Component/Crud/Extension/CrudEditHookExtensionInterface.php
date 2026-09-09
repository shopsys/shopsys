<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Extension;

use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Throwable;

interface CrudEditHookExtensionInterface extends CrudHookableExtensionInterface
{
    public function beforeEdit(Presentable $entity, object $data): void;

    public function afterEdit(Presentable $entity, object $data): void;

    public function onEditError(Presentable $entity, object $data, Throwable $exception): void;
}
