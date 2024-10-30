<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

final class CrudConfigData
{
    public array $customPageTitles = [
        PageType::CREATE->value => null,
        PageType::EDIT->value => null,
        PageType::LIST->value => null,
        PageType::DETAIL->value => null,
    ];

    public ?string $menuTitle = null;

    public array $defaultActions = [PageType::LIST];

    public string $menuSection = 'root';

    public ?string $submenuSection = null;

    public bool $visibleInMenu = true;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return string|null
     */
    public function getTitle(PageType $pageType): ?string
    {
        return $this->customPageTitles[$pageType->value] ?? 'test'; // TODO: hotfix for menu, rewrite to generate page titles based on entity class
    }

    /**
     * @return string|null
     */
    public function getMenuTitle(): ?string
    {
        if ($this->menuTitle !== null) {
            return $this->menuTitle;
        }

        return $this->getTitle(PageType::LIST);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\PageType[]
     */
    public function getDefaultActions(): array
    {
        return $this->defaultActions;
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
        return $this->visibleInMenu;
    }
}
