<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;

final class CrudConfigData
{
    private string $entityName;

    public array $customPageTitles = [
        ActionType::CREATE->value => null,
        ActionType::EDIT->value => null,
        ActionType::LIST->value => null,
        ActionType::DETAIL->value => null,
    ];

    public ?string $menuTitle = null;

    public bool $fullDisabled = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopsys\AdministrationBundle\Component\Config\ActionType>
     */
    private ArrayCollection $enabledActions;

    public string $menuSection = 'root';

    public ?string $submenuSection = null;

    public bool $visibleInMenu = true;

    public ?string $routePrefix = null;

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param class-string $entityClass
     */
    public function __construct(private string $crudController, private string $entityClass)
    {
        $this->entityName = (new ReflectionClass($entityClass))->getShortName();
        $this->enabledActions = new ArrayCollection([
            ActionType::LIST,
            ActionType::CREATE,
            ActionType::EDIT,
            ActionType::DETAIL,
            ActionType::DELETE,
        ]);

        $this->customPageTitles = [
            ActionType::CREATE->value => t('Creating new %entity_name%', ['%entity_name%' => $this->entityName]),
            ActionType::EDIT->value => t('Editing %entity_name%', ['%entity_name%' => $this->entityName]),
            ActionType::LIST->value => t('%entity_name% Overview', ['%entity_name%' => $this->entityName]),
            ActionType::DETAIL->value => t('Viewing %entity_name%', ['%entity_name%' => $this->entityName]),
        ];

        $this->menuTitle = t('%entity_name% Overview', ['%entity_name%' => $this->entityName]);
    }

    /**
     * @return string
     */
    public function getCrudController(): string
    {
        return $this->crudController;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $pageType
     * @return string
     */
    public function getTitle(ActionType $pageType): string
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
     * @return \Shopsys\AdministrationBundle\Component\Config\ActionType[]
     */
    public function getActions(): array
    {
        if ($this->fullDisabled === true) {
            return [];
        }

        return $this->enabledActions->getValues();
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     */
    public function enableAction(ActionType $actionType): void
    {
        if ($this->enabledActions->contains($actionType)) {
            return;
        }

        $this->enabledActions->add($actionType);
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @return bool
     */
    public function isActionEnabled(ActionType $actionType): bool
    {
        return $this->enabledActions->contains($actionType);
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     */
    public function disableAction(ActionType $actionType): void
    {
        if (!$this->enabledActions->contains($actionType)) {
            return;
        }

        $this->enabledActions->removeElement($actionType);
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
        return $this->visibleInMenu && $this->enabledActions->contains(ActionType::LIST);
    }

    /**
     * @return string|null
     */
    public function getRoutePrefix(): ?string
    {
        return $this->routePrefix;
    }
}
