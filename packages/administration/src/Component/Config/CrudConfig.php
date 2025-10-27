<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Webmozart\Assert\Assert;

final class CrudConfig
{
    /**
     * @var array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, string>
     */
    private array $customPageTitles;

    public ?string $menuTitle = null;

    private bool $fullDisabled = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopsys\AdministrationBundle\Component\Config\ActionType>
     */
    private ArrayCollection $enabledActions;

    private string $menuSection = 'root';

    private ?string $submenuSection = null;

    private bool $visibleInMenu = true;

    private ?string $routePrefix = null;

    /**
     * @param string $entityName
     */
    public function __construct(string $entityName)
    {
        $this->customPageTitles = [
            ActionType::CREATE->value => t('Creating new %entity_name%', ['%entity_name%' => $entityName]),
            ActionType::EDIT->value => t('Editing %entity_name%', ['%entity_name%' => $entityName]),
            ActionType::LIST->value => t('%entity_name% Overview', ['%entity_name%' => $entityName]),
            ActionType::DETAIL->value => t('Viewing %entity_name%', ['%entity_name%' => $entityName]),
        ];

        $this->enabledActions = new ArrayCollection([
            ActionType::LIST,
        ]);

        $this->menuTitle = t('%entity_name% Overview', ['%entity_name%' => $entityName]);
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
        $this->customPageTitles[$actionType->value] = $title;

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
        $this->menuTitle = $menuTitle;

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
            if ($this->enabledActions->contains($action)) {
                continue;
            }

            $this->enabledActions->add($action);
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
            $this->enabledActions->removeElement($action);
        }

        return $this;
    }

    /**
     * Sets where the crud controller will be displayed in the side menu.
     *
     * @param string $menuSection Name of root level menu section
     * @param string|null $submenuSection Name of submenu section
     * @return $this
     */
    public function setMenuSection(string $menuSection, ?string $submenuSection = null): self
    {
        $this->menuSection = $menuSection;
        $this->submenuSection = $submenuSection;

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
        $this->visibleInMenu = $visible;

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
        $this->fullDisabled = $disabled;

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
        $this->routePrefix = $routePrefix;

        return $this;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfigData
     */
    public function getConfig(): CrudConfigData
    {
        return new CrudConfigData(
            $this->customPageTitles,
            $this->menuTitle,
            $this->fullDisabled,
            $this->enabledActions->toArray(),
            $this->menuSection,
            $this->submenuSection,
            $this->visibleInMenu,
            $this->routePrefix,
        );
    }
}
