<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action\RouteData;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

final class CrudActionRouteData implements ActionRouteInterface
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|string $action built-in action or the name of a custom action
     * @param null|\Closure(mixed): (int|array<string, mixed>) $parameters returns the entity ID or all route parameters of the action
     */
    public function __construct(
        private readonly string $crudController,
        private readonly ActionType|string $action,
        private readonly ?Closure $parameters = null,
    ) {
    }

    /**
     * @return class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>
     */
    public function getCrudController(): string
    {
        return $this->crudController;
    }

    public function getActionName(): string
    {
        return CrudActionDefinition::normalizeName($this->action);
    }

    public function getRouteName(): string
    {
        $controllerName = ReflectionHelper::getShortClassName($this->getCrudController());

        return CrudTransformationHelper::generateRouteName($controllerName, $this->getActionName());
    }

    /**
     * Returns the route parameters for the given data, an integer returned by the closure is the ID of the record
     *
     * @return array<string, mixed>
     */
    public function getParameters(mixed $data = null): array
    {
        if ($this->parameters === null) {
            return [];
        }

        $parameters = ($this->parameters)($data);

        return is_int($parameters) ? ['id' => $parameters] : $parameters;
    }
}
