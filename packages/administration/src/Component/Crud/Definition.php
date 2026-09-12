<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use InvalidArgumentException;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
use Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition;
use Shopsys\AdministrationBundle\Component\Crud\Action\Exception\CrudActionNotFoundException;
use Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\ReadHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;

final readonly class Definition
{
    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param class-string $entityClass
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[] $extensions
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, \Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface|null> $handlers
     * @param array<string, \Shopsys\AdministrationBundle\Component\Crud\Action\CrudActionDefinition> $actions built-in and custom actions indexed by name
     */
    public function __construct(
        public string $controllerClass,
        public string $controllerName,
        public string $entityClass,
        public string $entityName,
        private CrudConfigData $config,
        private array $extensions,
        private array $handlers,
        private array $actions = [],
    ) {
        foreach ($handlers as $handler) {
            if ($handler instanceof ReadHandlerInterface && is_subclass_of($this->entityClass, Presentable::class) === false) {
                throw new RuntimeException(
                    sprintf(
                        'Entity "%s" must implement "%s" to be used with "%s".',
                        $this->entityClass,
                        Presentable::class,
                        $handler::class,
                    ),
                );
            }
        }
    }

    /**
     * @template T of object
     * @param class-string<T>|null $hookableInterface
     * @return ($hookableInterface is null
     *     ? array<\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension>
     *     : array<T&\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension&\Shopsys\AdministrationBundle\Component\Crud\Extension\CrudHookableExtensionInterface>
     * )
     */
    public function getExtensions(?string $hookableInterface = null): array
    {
        if ($hookableInterface === null) {
            return $this->extensions;
        }

        return array_filter(
            $this->extensions,
            fn ($extension) => $extension instanceof $hookableInterface,
        );
    }

    public function getConfig(): CrudConfigData
    {
        return $this->config;
    }

    public function getRoleConstant(): string
    {
        return CrudTransformationHelper::generateRoleConstant($this->controllerName, $this->getConfig()->getCustomRoleConstant());
    }

    /**
     * Returns the definition of a built-in or custom action, custom actions are looked up by name
     *
     * @throws \Shopsys\AdministrationBundle\Component\Crud\Action\Exception\CrudActionNotFoundException
     */
    public function getAction(ActionType|string $action): CrudActionDefinition
    {
        $actionName = CrudActionDefinition::normalizeName($action);

        return $this->actions[$actionName] ?? throw CrudActionNotFoundException::create(
            $this->controllerClass,
            $actionName,
            array_keys($this->actions),
        );
    }

    /**
     * Returns the first registered handler able to load a single record, so custom actions can use the same loading
     * (including the checks the handler does) as the built-in edit and delete actions
     */
    public function getReadHandler(): ReadHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof ReadHandlerInterface) {
                return $handler;
            }
        }

        throw new RuntimeException(sprintf(
            'No handler implementing "%s" is registered for "%s". Register one via CrudConfig::registerHandler().',
            ReadHandlerInterface::class,
            $this->controllerClass,
        ));
    }

    public function getHandlerForAction(ActionType $actionType): HandlerInterface
    {
        if (array_key_exists($actionType->value, $this->handlers) === false) {
            throw new InvalidArgumentException(sprintf('"%s" action does not support handlers.', $actionType->value));
        }

        if ($this->handlers[$actionType->value] === null) {
            throw new RuntimeException(sprintf('Handler for "%s" action is not registered.', $actionType->value));
        }

        return $this->handlers[$actionType->value];
    }
}
