<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Action;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

/**
 * Describes one action of a CRUD controller (built-in or custom, both declared by the CrudAction attribute):
 * where it is handled, how its route looks and which access control rules guard it
 */
final readonly class CrudActionDefinition
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudControllerClass
     * @param class-string $controllerClass class handling the action (the CRUD controller itself, its extension or any other controller)
     * @param string $path route path appended after the CRUD controller URL
     * @param bool $entityBound whether the route works with a single record ({id} parameter)
     * @param string[] $methods
     * @param array<string, string> $requirements
     * @param array<string, mixed> $defaults
     * @param list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData> $accessControlRules resolved at build time by CrudAttributeProcessor
     */
    public function __construct(
        public string $name,
        public string $crudControllerClass,
        public string $controllerClass,
        public string $method,
        public string $path,
        public bool $entityBound,
        public array $methods = [],
        public array $requirements = [],
        public array $defaults = [],
        public ?string $condition = null,
        public array $accessControlRules = [],
    ) {
    }

    public static function normalizeName(ActionType|string $action): string
    {
        return $action instanceof ActionType ? $action->value : $action;
    }

    /**
     * @param list<\Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleData> $accessControlRules
     */
    public function withAccessControlRules(array $accessControlRules): self
    {
        return new self(
            $this->name,
            $this->crudControllerClass,
            $this->controllerClass,
            $this->method,
            $this->path,
            $this->entityBound,
            $this->methods,
            $this->requirements,
            $this->defaults,
            $this->condition,
            $accessControlRules,
        );
    }

    /**
     * Returns the controller reference in the "Class::method" format used by the router
     */
    public function getControllerReference(): string
    {
        return $this->controllerClass . '::' . $this->method;
    }

    public function getRouteName(): string
    {
        return CrudTransformationHelper::generateRouteName(
            ReflectionHelper::getShortClassName($this->crudControllerClass),
            $this->name,
        );
    }

    /**
     * Returns the array stored in the container parameter by LoadCrudActionsCompilerPass
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'crudControllerClass' => $this->crudControllerClass,
            'controllerClass' => $this->controllerClass,
            'method' => $this->method,
            'path' => $this->path,
            'entityBound' => $this->entityBound,
            'methods' => $this->methods,
            'requirements' => $this->requirements,
            'defaults' => $this->defaults,
            'condition' => $this->condition,
            'accessControlRules' => array_map(static fn (AccessControlRuleData $rule): array => $rule->toArray(), $this->accessControlRules),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            crudControllerClass: $data['crudControllerClass'],
            controllerClass: $data['controllerClass'],
            method: $data['method'],
            path: $data['path'],
            entityBound: $data['entityBound'],
            methods: $data['methods'],
            requirements: $data['requirements'],
            defaults: $data['defaults'],
            condition: $data['condition'],
            accessControlRules: array_map(AccessControlRuleData::fromArray(...), $data['accessControlRules']),
        );
    }
}
