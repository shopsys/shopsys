<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Action;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Symfony\Component\String\UnicodeString;

/**
 * Finds the actions declared by the CrudAction attribute in a class (the built-in actions of AbstractCrudController,
 * custom actions of CRUD controllers, their extensions, other controllers and invokable classes) and validates the declarations.
 * The attribute is looked up along the prototype chain, so an overriding method keeps the declaration of the parent.
 */
final class CrudActionDiscoverer
{
    private const string ACTION_NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    private const string INVOKE_METHOD = '__invoke';

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>[] $crudControllerClasses registered CRUD controllers
     * @param array<class-string, class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>> $controllerClassesByExtensionClass
     * @return list<\Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition> without access control rules
     */
    public function discover(
        ReflectionClass $class,
        array $crudControllerClasses,
        array $controllerClassesByExtensionClass,
    ): array {
        $actions = [];

        foreach ($this->findActionMethods($class) as [$attribute, $method]) {
            $actions[] = $this->createAction($class, $method, $attribute, $crudControllerClasses, $controllerClassesByExtensionClass);
        }

        return $actions;
    }

    /**
     * Returns the CrudAction attributes with the methods they apply to. A class-level attribute applies to __invoke().
     *
     * @return iterable<array{\Shopsys\AdministrationBundle\Component\Attributes\CrudAction, \ReflectionMethod}>
     */
    private function findActionMethods(ReflectionClass $class): iterable
    {
        foreach ($class->getAttributes(CrudAction::class) as $classAttribute) {
            if (!$class->hasMethod(self::INVOKE_METHOD)) {
                throw new RuntimeException(sprintf(
                    'Class "%s" has the %s attribute but no %s() method. Put the attribute on the handling method instead.',
                    $class->getName(),
                    CrudAction::class,
                    self::INVOKE_METHOD,
                ));
            }

            yield [$classAttribute->newInstance(), $class->getMethod(self::INVOKE_METHOD)];
        }

        foreach ($class->getMethods() as $method) {
            foreach (ReflectionHelper::getMethodAttributes($method, CrudAction::class) as $methodAttribute) {
                yield [$methodAttribute, $method];
            }
        }
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>[] $crudControllerClasses
     * @param array<class-string, class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>> $controllerClassesByExtensionClass
     */
    private function createAction(
        ReflectionClass $class,
        ReflectionMethod $method,
        CrudAction $attribute,
        array $crudControllerClasses,
        array $controllerClassesByExtensionClass,
    ): CrudActionDefinition {
        if (!$method->isPublic()) {
            throw new RuntimeException(sprintf('CRUD action method %s must be public.', $this->describeMethod($class, $method)));
        }

        // an attribute of an unknown class (e.g. a missing use statement) would be silently ignored by the access control
        foreach ($method->getAttributes() as $methodAttribute) {
            if (!class_exists($methodAttribute->getName())) {
                throw new RuntimeException(sprintf(
                    'CRUD action %s: the attribute class "%s" does not exist, check the use statements.',
                    $this->describeMethod($class, $method),
                    $methodAttribute->getName(),
                ));
            }
        }

        $name = $this->resolveName($class, $method, $attribute);
        $crudControllerClass = $this->resolveCrudControllerClass($class, $method, $attribute, $crudControllerClasses, $controllerClassesByExtensionClass);
        $placeholderParameterNames = $this->getRoutePlaceholderParameterNames($method);
        $path = $attribute->path ?? $this->createDefaultPath($name, $placeholderParameterNames);

        foreach ($placeholderParameterNames as $parameterName) {
            if (!str_contains($path, '{' . $parameterName . '}')) {
                throw new RuntimeException(sprintf(
                    'CRUD action %s: the required parameter $%s is not a placeholder of the route path "%s", add {%s} to the path, give the parameter a default value or resolve it by an attribute.',
                    $this->describeMethod($class, $method),
                    $parameterName,
                    $path,
                    $parameterName,
                ));
            }
        }

        foreach (array_keys($attribute->defaults) as $defaultName) {
            if ($defaultName === '_controller' || str_starts_with($defaultName, CrudRouteProvider::CRUD_DEFAULTS_PREFIX)) {
                throw new RuntimeException(sprintf(
                    'CRUD action %s: the route default "%s" is reserved for the CRUD routing and cannot be set.',
                    $this->describeMethod($class, $method),
                    $defaultName,
                ));
            }
        }

        return new CrudActionDefinition(
            name: $name,
            crudControllerClass: $crudControllerClass,
            controllerClass: $class->getName(),
            method: $method->getName(),
            path: '/' . ltrim($path, '/'),
            entityBound: str_contains($path, '{id}'),
            methods: $attribute->methods,
            requirements: $attribute->requirements,
            defaults: $attribute->defaults,
            condition: $attribute->condition,
        );
    }

    private function resolveName(ReflectionClass $class, ReflectionMethod $method, CrudAction $attribute): string
    {
        if ($attribute->name === null && $method->getName() === self::INVOKE_METHOD) {
            throw new RuntimeException(sprintf(
                'CRUD action %s: the action name cannot be derived from %s(), set it in the %s attribute.',
                $this->describeMethod($class, $method),
                self::INVOKE_METHOD,
                CrudAction::class,
            ));
        }

        $name = $attribute->name ?? (string)new UnicodeString(preg_replace('/Action$/', '', $method->getName()))->snake();

        if (preg_match(self::ACTION_NAME_PATTERN, $name) !== 1) {
            throw new RuntimeException(sprintf(
                'CRUD action %s: the action name "%s" must be in snake_case (%s).',
                $this->describeMethod($class, $method),
                $name,
                self::ACTION_NAME_PATTERN,
            ));
        }

        return $name;
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>[] $crudControllerClasses
     * @param array<class-string, class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>> $controllerClassesByExtensionClass
     * @return class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController>
     */
    private function resolveCrudControllerClass(
        ReflectionClass $class,
        ReflectionMethod $method,
        CrudAction $attribute,
        array $crudControllerClasses,
        array $controllerClassesByExtensionClass,
    ): string {
        $className = $class->getName();

        if ($attribute->crudController !== null) {
            $crudControllerClass = $attribute->crudController;
        } elseif (is_subclass_of($className, AbstractCrudController::class)) {
            $crudControllerClass = $className;
        } elseif (is_subclass_of($className, AbstractCrudControllerExtension::class) && isset($controllerClassesByExtensionClass[$className])) {
            $crudControllerClass = $controllerClassesByExtensionClass[$className];
        } else {
            throw new RuntimeException(sprintf(
                'CRUD action %s: the target CRUD controller cannot be inferred, set the "crudController" argument of the %s attribute (only methods of a CRUD controller or of its %s extension infer it).',
                $this->describeMethod($class, $method),
                CrudAction::class,
                CrudControllerExtension::class,
            ));
        }

        if (!in_array($crudControllerClass, $crudControllerClasses, true)) {
            throw new RuntimeException(sprintf(
                'CRUD action %s: "%s" is not a registered CRUD controller.',
                $this->describeMethod($class, $method),
                $crudControllerClass,
            ));
        }

        return $crudControllerClass;
    }

    /**
     * Returns the names of the parameters that have to come from the route: required parameters of a scalar type
     * without an attribute. Everything else is resolved by Symfony (Request, services, entities, parameters mapped
     * by an attribute such as MapQueryParameter) or has a default value.
     *
     * @return list<string>
     */
    private function getRoutePlaceholderParameterNames(ReflectionMethod $method): array
    {
        $parameterNames = [];

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (
                $parameter->isDefaultValueAvailable()
                || $parameter->getAttributes() !== []
                || !($type instanceof ReflectionNamedType)
                || !$type->isBuiltin()
                || in_array($type->getName(), ['array', 'iterable', 'callable', 'object', 'mixed'], true)
            ) {
                continue;
            }

            $parameterNames[] = $parameter->getName();
        }

        return $parameterNames;
    }

    /**
     * @param list<string> $placeholderParameterNames
     */
    private function createDefaultPath(string $name, array $placeholderParameterNames): string
    {
        $path = '/' . new UnicodeString($name)->kebab();

        foreach ($placeholderParameterNames as $parameterName) {
            $path .= '/{' . $parameterName . '}';
        }

        return $path;
    }

    private function describeMethod(ReflectionClass $class, ReflectionMethod $method): string
    {
        return $class->getName() . '::' . $method->getName() . '()';
    }
}
