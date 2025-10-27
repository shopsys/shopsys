<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

final readonly class CrudConfigData
{
    /**
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, string> $customPageTitles
     * @param string|null $menuTitle
     * @param bool $fullDisabled
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType[] $enabledActions
     * @param string $menuSection
     * @param string|null $submenuSection
     * @param bool $visibleInMenu
     * @param string|null $routePrefix
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
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     * @return string
     */
    public function getTitle(ActionType $pageType): string
    {
        return $this->customPageTitles[$pageType->value] ?? '';
    }

    /**
     * @return string
     */
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

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @return bool
     */
    public function isActionEnabled(ActionType $actionType): bool
    {
        return in_array($actionType, $this->enabledActions, true);
    }

    /**
     * @return bool
     */
    public function isFullDisabled(): bool
    {
        return $this->fullDisabled;
    }

    /**
     * @return string
     */
    public function getMenuSection(): string
    {
        return $this->menuSection;
    }

    /**
     * @return string|null
     */
    public function getSubmenuSection(): ?string
    {
        return $this->submenuSection;
    }

    /**
     * @return bool
     */
    public function isVisibleInMenu(): bool
    {
        return $this->visibleInMenu && $this->isActionEnabled(ActionType::LIST);
    }

    /**
     * @return string|null
     */
    public function getRoutePrefix(): ?string
    {
        return $this->routePrefix;
    }
}
