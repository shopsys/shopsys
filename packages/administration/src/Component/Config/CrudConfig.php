<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Webmozart\Assert\Assert;

final class CrudConfig
{
    private CrudConfigData $crudConfigData;

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param class-string $entityClass
     */
    public function __construct(string $crudController, string $entityClass)
    {
        $this->crudConfigData = new CrudConfigData($crudController, $entityClass);
    }

    /**
     * Sets a custom title for a given action type.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param string $title
     * @return $this
     */
    public function setTitle(ActionType $actionType, string $title): self
    {
        $this->crudConfigData->customPageTitles[$actionType->value] = $title;

        return $this;
    }

    /**
     * Sets the title of the menu item that will be used.
     *
     * @param string $menuTitle
     * @return $this
     */
    public function setMenuTitle(string $menuTitle): self
    {
        $this->crudConfigData->menuTitle = $menuTitle;

        return $this;
    }

    /**
     * Enables a given action(s) for the crud controller.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|\Shopsys\AdministrationBundle\Component\Config\ActionType[] $actions
     * @return $this
     */
    public function enableAction(ActionType|array $actions): self
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }

        Assert::allIsInstanceOf($actions, ActionType::class, 'The given action is not a valid action type');

        foreach ($actions as $action) {
            $this->crudConfigData->enableAction($action);
        }

        return $this;
    }

    /**
     * Disables a given action(s) for the crud controller.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|\Shopsys\AdministrationBundle\Component\Config\ActionType[] $actions
     * @return $this
     */
    public function disableAction(ActionType|array $actions): self
    {
        if (!is_array($actions)) {
            $actions = [$actions];
        }

        Assert::allIsInstanceOf($actions, ActionType::class, 'The given action is not a valid action type');

        foreach ($actions as $action) {
            $this->crudConfigData->disableAction($action);
        }

        return $this;
    }

    /**
     * TODO: Do something with $menuSection to be able to use some Enum instead of string
     *
     * Sets where the crud controller will be displayed in the side menu.
     *
     * @param string $menuSection Name of root level menu section
     * @param string|null $submenuSection Name of submenu section
     * @return $this
     */
    public function setMenuSection(string $menuSection, ?string $submenuSection = null): self
    {
        $this->crudConfigData->menuSection = $menuSection;
        $this->crudConfigData->submenuSection = $submenuSection;

        return $this;
    }

    /**
     * Show or hide the crud controller in the side menu.
     *
     * @param bool $visible
     * @return $this
     */
    public function visibleInMenu(bool $visible): self
    {
        $this->crudConfigData->visibleInMenu = $visible;

        return $this;
    }

    /**
     * Disable the CRUD controller with all its actions and pages.
     *
     * @param bool $disabled
     * @return $this
     */
    public function disable(bool $disabled): self
    {
        $this->crudConfigData->fullDisabled = $disabled;

        return $this;
    }

    /**
     * Set custom route prefix for the CRUD controller. This will be used as a prefix for all routes
     *
     * Example: You have `RoleGroupsController` and you set route prefix to `/administrators` then the route will be `/admin/administrators/role-groups/`
     *
     * @param string|null $routePrefix
     * @return $this
     */
    public function setRoutePrefix(?string $routePrefix): self
    {
        $this->crudConfigData->routePrefix = $routePrefix;

        return $this;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    public function getConfig(): CrudConfigData
    {
        return $this->crudConfigData;
    }
}
