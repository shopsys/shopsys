<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use RuntimeException;

final readonly class CrudConfigData
{
    /**
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, string> $customPageTitles
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType[] $enabledActions
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, null|class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface>> $handlerClasses
     */
    public function __construct(
        private array $customPageTitles,
        private ?string $menuTitle,
        private bool $fullDisabled,
        private array $enabledActions,
        private string $menuSection,
        private ?string $submenuSection,
        private bool $visibleInMenu,
        private ?string $routePrefix,
        private ?string $customRoleConstant,
        private ?string $customRoleSection,
        private array $handlerClasses,
        private ?string $menuIcon,
    ) {
        foreach ($this->enabledActions as $action) {
            if (array_key_exists($action->value, $this->handlerClasses) && $this->handlerClasses[$action->value] === null) {
                throw new RuntimeException(sprintf(
                    'Enabling "%s" action requires corresponding handler to be registered. Use "$crudConfig->registerHandler()" to register the required handler first.',
                    $action->value,
                ));
            }
        }
    }

    public function getTitle(ActionType $pageType): string
    {
        return $this->customPageTitles[$pageType->value] ?? '';
    }

    public function getMenuTitle(): string
    {
        return $this->menuTitle;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\ActionType[]
     */
    public function getActions(): array
    {
        if ($this->fullDisabled === true) {
            return [];
        }

        return $this->enabledActions;
    }

    public function isActionEnabled(ActionType $actionType): bool
    {
        return in_array($actionType, $this->enabledActions, true);
    }

    public function isFullDisabled(): bool
    {
        return $this->fullDisabled;
    }

    public function getMenuSection(): string
    {
        return $this->menuSection;
    }

    public function getSubmenuSection(): ?string
    {
        return $this->submenuSection;
    }

    public function isVisibleInMenu(): bool
    {
        return $this->visibleInMenu && $this->isActionEnabled(ActionType::LIST);
    }

    public function getRoutePrefix(): ?string
    {
        return $this->routePrefix;
    }

    public function getCustomRoleConstant(): ?string
    {
        return $this->customRoleConstant;
    }

    public function getCustomRoleSection(): ?string
    {
        return $this->customRoleSection;
    }

    /**
     * @return array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface>>
     */
    public function getHandlerClasses(): array
    {
        return $this->handlerClasses;
    }

    public function getMenuIcon(): ?string
    {
        return $this->menuIcon;
    }
}
