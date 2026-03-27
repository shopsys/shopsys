<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud;

use InvalidArgumentException;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigData;
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
     */
    public function __construct(
        public string $controllerClass,
        public string $controllerName,
        public string $entityClass,
        public string $entityName,
        private CrudConfigData $config,
        private array $extensions,
        private array $handlers,
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
        return $this->getConfig()->getCustomRoleConstant() ?? 'ROLE_CRUD_' . strtoupper(CrudTransformationHelper::transformToRouteName($this->controllerName));
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
