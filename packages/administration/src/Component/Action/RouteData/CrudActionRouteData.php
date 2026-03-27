<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action\RouteData;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

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

    public function getRouteName(): string
    {
        $controllerName = ReflectionHelper::getShortClassName($this->getCrudController());

        return CrudTransformationHelper::generateRouteName($controllerName, $this->getActionType());
    }

    public function getId(mixed $data = null): ?int
    {
        if ($this->id === null) {
            return null;
        }

        return call_user_func($this->id, $data);
    }
}
