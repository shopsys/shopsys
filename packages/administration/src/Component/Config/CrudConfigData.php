<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

final class CrudConfigData
{
    public string $entityName;

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

    public function __construct(string $entityName)
    {
        $this->entityName = $entityName;

        $this->customPageTitles = [
            PageType::CREATE->value => t('Creating new %entity_name%', ['%entity_name%' => $entityName]),
            PageType::EDIT->value => t('Editing %entity_name%', ['%entity_name%' => $entityName]),
            PageType::LIST->value => t('%entity_name% Overview', ['%entity_name%' => $entityName]),
            PageType::DETAIL->value => t('Viewing %entity_name%', ['%entity_name%' => $entityName]),
        ];

        $this->menuTitle = t('%entity_name% Overview', ['%entity_name%' => $entityName]);
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return string
     */
    public function getTitle(PageType $pageType): string
    {
        return $this->customPageTitles[$pageType->value];
    }

    /**
     * @return string
     */
    public function getMenuTitle(): string
    {
        return $this->menuTitle;
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
