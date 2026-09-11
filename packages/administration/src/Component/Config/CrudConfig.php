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

    /**
     * @var string[]
     */
    private array $disabledCustomActionNames = [];

    /**
     * @param string|null $customRoleConstant role declared by the ForRole attribute on the CRUD controller (or its extension), resolved at compile time
     * @param string[] $customActionNames names of the custom actions declared for the CRUD controller by the CrudAction attribute, resolved at compile time
     */
    public function __construct(
        private readonly string $entityName,
        private readonly ?string $customRoleConstant = null,
        private readonly array $customActionNames = [],
    ) {
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
     * Enables a given action(s) for the crud controller. Custom actions are enabled by default and are referenced by their name.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|string|array<\Shopsys\AdministrationBundle\Component\Config\ActionType|string> $actions
     * @return $this
     */
    public function enableAction(ActionType|string|array $actions): self
    {
        foreach ($this->normalizeActions($actions) as $action) {
            if (is_string($action)) {
                $this->disabledCustomActionNames = array_values(array_diff($this->disabledCustomActionNames, [$action]));

                continue;
            }

            if ($this->enabledActions->contains($action)) {
                continue;
            }

            $this->enabledActions->add($action);
        }

        return $this;
    }

    /**
     * Disables a given action(s) for the crud controller. Custom actions are referenced by their name.
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|string|array<\Shopsys\AdministrationBundle\Component\Config\ActionType|string> $actions
     * @return $this
     */
    public function disableAction(ActionType|string|array $actions): self
    {
        foreach ($this->normalizeActions($actions) as $action) {
            if (is_string($action)) {
                if (!in_array($action, $this->disabledCustomActionNames, true)) {
                    $this->disabledCustomActionNames[] = $action;
                }

                continue;
            }

            $this->enabledActions->removeElement($action);
        }

        return $this;
    }

    /**
     * Built-in actions given by name are converted to ActionType, custom action names are validated against the declared ones
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType|string|array<\Shopsys\AdministrationBundle\Component\Config\ActionType|string> $actions
     * @return array<\Shopsys\AdministrationBundle\Component\Config\ActionType|string>
     */
    private function normalizeActions(ActionType|string|array $actions): array
    {
        $normalizedActions = [];

        foreach (is_array($actions) ? $actions : [$actions] as $action) {
            if ($action instanceof ActionType) {
                $normalizedActions[] = $action;

                continue;
            }

            Assert::string($action, 'The given action is not a valid action type or custom action name');
            $builtInAction = ActionType::tryFrom($action);

            if ($builtInAction !== null) {
                $normalizedActions[] = $builtInAction;

                continue;
            }

            if (!in_array($action, $this->customActionNames, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown custom action "%s" for "%s". Declared custom actions: %s.',
                    $action,
                    $this->entityName,
                    $this->customActionNames === [] ? 'none' : implode(', ', $this->customActionNames),
                ));
            }

            $normalizedActions[] = $action;
        }

        return $normalizedActions;
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
            $this->customActionNames,
            $this->disabledCustomActionNames,
        );
    }
}
