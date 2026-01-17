<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

final readonly class CrudConfigData
{
    /**
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, string> $customPageTitles
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType[] $enabledActions
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
    ) {
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
}
