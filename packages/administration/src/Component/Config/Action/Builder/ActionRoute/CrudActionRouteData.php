<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\ActionType;

class CrudActionRouteData implements ActionRouteInterface
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param null|\Closure(?object $entity): int $id
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

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\ActionType
     */
    public function getActionType(): ActionType
    {
        return $this->actionType;
    }

    /**
     * @param object|null $entity
     * @return int|null
     */
    public function getId(?object $entity = null): ?int
    {
        if ($this->id === null) {
            return null;
        }

        return call_user_func($this->id, $entity);
    }
}
