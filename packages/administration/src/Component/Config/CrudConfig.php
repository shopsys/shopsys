<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type MenuItemPosition from \Shopsys\FrameworkBundle\Model\AdminNavigation\MenuItemPositioner
 */
final class CrudConfig
{
    private ?string $entityNameSingular = null;

    private ?string $entityNamePlural = null;

    private ?string $menuTitle = null;

    private bool $fullDisabled = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopsys\AdministrationBundle\Component\Config\ActionType>
     */
    private ArrayCollection $enabledActions;

    private string $menuSection = 'root';

    private ?string $submenuSection = null;

    /**
     * @var MenuItemPosition
     */
    private string|array $menuSectionPosition = 'last';

    private bool $visibleInMenu = true;

    private ?string $routePrefix = null;

    private ?string $customRoleConstant = null;

    private ?string $customRoleSection = null;

    private ?string $menuIcon = null;

    private ?CrudListDomainControl $listDomainControl = null;

    /**
     * @var int[]|null
     */
    private ?array $listAllowedDomainIds = null;

    /**
     * @var array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface>|null>
     */
    private array $handlerClasses = [
        ActionType::DELETE->value => null,
        ActionType::EDIT->value => null,
        ActionType::CREATE->value => null,
    ];

    public function __construct(private readonly string $entityName)
    {
        $this->enabledActions = new ArrayCollection([
            ActionType::LIST,
        ]);
    }

    /**
     * Overrides the automatically derived singular entity name. Wrap the value in `t()`.
     *
     * @return $this
     */
    public function setEntityNameSingular(string $entityNameSingular): self
    {
        $this->entityNameSingular = $entityNameSingular;

        return $this;
    }

    /**
     * Overrides the automatically derived plural entity name. Wrap the value in `t()`.
     *
     * @return $this
     */
    public function setEntityNamePlural(string $entityNamePlural): self
    {
        $this->entityNamePlural = $entityNamePlural;

        return $this;
    }

    /**
     * Sets the title of the menu item that will be used.
     *
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
     * @param MenuItemPosition $position Position of the item among its siblings
     * @return $this
     */
    public function setMenuSection(
        string $menuSection,
        ?string $submenuSection = null,
        string|array $position = 'last',
    ): self {
        $this->menuSection = $menuSection;
        $this->submenuSection = $submenuSection;
        $this->menuSectionPosition = $position;

        return $this;
    }

    /**
     * Show or hide the crud controller in the side menu.
     *
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
     * @return $this
     */
    public function setRoutePrefix(?string $routePrefix): self
    {
        $this->routePrefix = $routePrefix;

        return $this;
    }

    /**
     * Set custom role constant for the CRUD controller. This will be used for access control checks.
     * If not set, role constant will be generated from the controller name automatically.
     *
     * @return $this
     */
    public function setCustomRoleConstant(?string $roleConstant): self
    {
        $this->customRoleConstant = $roleConstant;

        return $this;
    }

    /**
     * Set role section for role constant. If not set, role section will be got from menu section automatically.
     *
     * @see \Shopsys\AdministrationBundle\Component\Security\Role\AdminRoleSectionsProvider
     * @return $this
     */
    public function setCustomRoleSection(string $roleSection): self
    {
        $this->customRoleSection = $roleSection;

        return $this;
    }

    /**
     * Set icon for root-level menu item. Only applicable when menu section is 'root' (1st level).
     *
     * @return $this
     */
    public function setMenuIcon(string $icon): self
    {
        $this->menuIcon = $icon;

        return $this;
    }

    /**
     * Sets the domain control displayed on the list page.
     *
     * @param int[]|null $allowedDomainIds Domain IDs available in the quick domain filter. Null allows all domains available to the administrator.
     * @return $this
     */
    public function setListDomainControl(
        CrudListDomainControl $listDomainControl,
        ?array $allowedDomainIds = null,
    ): self {
        if ($listDomainControl === CrudListDomainControl::SWITCHER && $allowedDomainIds !== null) {
            throw new InvalidArgumentException('Domain switcher does not support allowed domain IDs.');
        }

        Assert::allInteger($allowedDomainIds ?? []);

        $this->listDomainControl = $listDomainControl;
        $this->listAllowedDomainIds = $allowedDomainIds;

        return $this;
    }

    /**
     * @template T of \Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface
     *
     * Register handler class or classes for CRUD actions.
     * @param array<class-string<T>>|class-string<T> $handler
     * @return $this
     */
    public function registerHandler(array|string $handler): self
    {
        $handlers = is_array($handler) ? $handler : [$handler];

        Assert::allClassExists($handlers);
        Assert::allImplementsInterface($handlers, HandlerInterface::class);

        foreach ($handlers as $handlerClass) {
            $actionTypes = ActionType::getActionsForHandlerClass($handlerClass);

            if (count($actionTypes) === 0) {
                throw new InvalidArgumentException(sprintf(
                    'Handler class "%s" does not correspond to any CRUD action.',
                    $handlerClass,
                ));
            }

            foreach ($actionTypes as $actionType) {
                if ($this->handlerClasses[$actionType->value] !== null) {
                    throw new RuntimeException(sprintf(
                        'Cannot register "%s" handler class. Handler for "%s" action is already registered by "%s" class.',
                        $handlerClass,
                        $actionType->value,
                        $this->handlerClasses[$actionType->value],
                    ));
                }

                $this->handlerClasses[$actionType->value] = $handlerClass;
                $this->enableAction($actionType);
            }
        }

        return $this;
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface> $handler
     * @return $this
     */
    public function unregisterHandler(string $handler): self
    {
        Assert::classExists($handler);
        Assert::implementsInterface($handler, HandlerInterface::class);

        $handlerExists = false;

        foreach ($this->handlerClasses as $actionType => $handlerClass) {
            if ($handlerClass === $handler) {
                $this->handlerClasses[$actionType] = null;
                $this->disableAction(ActionType::from($actionType));

                $handlerExists = true;
            }
        }

        if ($handlerExists === false) {
            throw new InvalidArgumentException(sprintf(
                'Handler class "%s" is not registered and cannot be unregistered.',
                $handler,
            ));
        }

        return $this;
    }

    public function getConfig(): CrudConfigData
    {
        return new CrudConfigData(
            $this->entityNameSingular,
            $this->entityNamePlural,
            $this->menuTitle,
            $this->entityName,
            $this->fullDisabled,
            $this->enabledActions->toArray(),
            $this->menuSection,
            $this->submenuSection,
            $this->menuSectionPosition,
            $this->visibleInMenu,
            $this->routePrefix,
            $this->customRoleConstant,
            $this->customRoleSection,
            $this->handlerClasses,
            $this->menuIcon,
            $this->listDomainControl,
            $this->listAllowedDomainIds,
        );
    }
}
