<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action\RouteData;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\ActionType;

class CrudActionRouteData implements ActionRouteInterface
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param null|\Closure(mixed): int $id
     */
    public function __construct(
        private readonly string $crudController,
        private readonly ActionType $actionType,
        private readonly ?Closure $id = null,
    ) {
    }

    /**
     * @return class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>
     */
    public function getCrudController(): string
    {
        return $this->crudController;
    }

    public function getActionType(): ActionType
    {
        return $this->actionType;
    }

    public function getId(mixed $data = null): ?int
    {
        if ($this->id === null) {
            return null;
        }

        return call_user_func($this->id, $data);
    }
}
