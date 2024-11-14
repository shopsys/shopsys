<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use ReflectionFunction;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Webmozart\Assert\Assert;

final class ActionBuilder
{
    private string $name;

    private ActionType $actionType;

    private ?string $label = null;

    private ?string $icon = null;

    private string $cssClass = '';

    /**
     * @var callable|null
     */
    private $displayIf = null;

    private ActionRouteType $routeType = ActionRouteType::NONE;
    private ?string $route = null;

    /**
     * @var array|callable
     */
    private $routeParameters = [];

    /**
     * @var callable|string|null
     */
    private $url = null;

    /**
     * @var callable|null
     */
    private $pageId = null;

    private ?string $crudController = null;

    private ?PageType $pageType = null;

    /**
     * @param string $name
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     */
    private function __construct(string $name, ActionType $actionType)
    {
        $this->name = $name;
        $this->actionType = $actionType;
    }

    /**
     * @param string $name
     * @param string $label
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     * @return self
     */
    private static function new(string $name, string $label, ActionType $actionType): self
    {
        $action = new self($name, $actionType);
        $action->setLabel($label);

        return $action;
    }

    /**
     * Create action without entity context. Will be displayed in the top bar
     *
     * @param string $name
     * @param string $label
     * @return self
     */
    public static function createGlobal(string $name, string $label): self
    {
        return self::new($name, $label, ActionType::GLOBAL);
    }

    /**
     * Create action with entity context.
     *
     * @param string $name
     * @param string $label
     * @return self
     */
    public static function createEntity(string $name, string $label): self
    {
        return self::new($name, $label, ActionType::ENTITY);
    }

    /**
     * Set name of action that will be shown to the users
     *
     * @param string $label
     * @return self
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set icon of action that will be shown next to label
     *
     * @param string $icon
     * @return self
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     *
     * Set function that will determine if action should be displayed
     *
     * @param callable(object $entity): bool|callable(): bool $function Function must return boolean value. If function returns false, action will not be displayed
     * @return $this
     */
    public function displayIf(callable $function): self
    {
        $reflectionFunction = new ReflectionFunction($function);

        if ($this->actionType === ActionType::GLOBAL) {
            Assert::same($reflectionFunction->getNumberOfParameters(), 0, 'Global actions does not support `displayIf` function with parameters');
        }

        if ($this->actionType === ActionType::ENTITY) {
            Assert::maxLength($reflectionFunction->getNumberOfParameters(), 1, 'Entity actions require `displayIf` function with one parameter of entity object');
        }

        $this->displayIf = $function;

        return $this;
    }

    /**
     * Set CSS class that will be added to action button
     *
     * @param string $cssClass
     * @return $this
     */
    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array or callable function that will return array.
     * For entity actions, closure provides entity object as parameter.
     *
     * @param string $route
     * @param array|callable(object $entity): array|callable(): array $parameters
     * @return $this
     */
    public function linkToRoute(string $route, array|callable $parameters = []): self
    {
        if (is_callable($parameters)) {
            $reflectionFunction = new ReflectionFunction($parameters);

            if ($this->actionType === ActionType::GLOBAL) {
                Assert::same($reflectionFunction->getNumberOfParameters(), 0, 'Global actions does not support `parameters` function with function parameters');
            }

            if ($this->actionType === ActionType::ENTITY) {
                Assert::maxLength($reflectionFunction->getNumberOfParameters(), 1, 'Entity actions require `parameters` function with one parameter of entity object');
            }
        }

        $this->routeType = ActionRouteType::ROUTE;
        $this->route = $route;
        $this->routeParameters = $parameters;

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * URL can be provided as string or callable function that will return string.
     * For entity actions, closure provides entity object as parameter.
     *
     * @param string|callable(object $entity): string|callable(): string $url
     * @return $this
     */
    public function linkToUrl(callable|string $url): self
    {
        if (is_callable($url)) {
            $reflectionFunction = new ReflectionFunction($url);

            if ($this->actionType === ActionType::GLOBAL) {
                Assert::same($reflectionFunction->getNumberOfParameters(), 0, 'Global actions does not support `parameters` function with function parameters');
            }

            if ($this->actionType === ActionType::ENTITY) {
                Assert::maxLength($reflectionFunction->getNumberOfParameters(), 1, 'Entity actions require `parameters` function with one parameter of entity object');
            }
        }

        $this->routeType = ActionRouteType::URL;
        $this->url = $url;

        return $this;
    }

    /**
     * Can be used to generate link to another CRUD controller. This will generate link to the CRUD controller with provided page type.
     * If you are linking to page type that requires entity ID, you must provide callable function that will return entity ID.
     *
     * @param string $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param null|callable(object $entity): int|callable(): int $id
     * @return $this
     */
    public function linkToCrud(string $crudController, PageType $pageType, ?callable $id = null): self
    {
        if (in_array($pageType, CrudRouteProvider::PAGES_WITH_ID, true) && $id === null) {
            throw new \InvalidArgumentException('Page type ' . $pageType->value . ' requires entity ID to be provided');
        }

        $this->routeType = ActionRouteType::CRUD;

        $this->crudController = $crudController;
        $this->pageType = $pageType;
        $this->pageId = $id;

        return $this;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData
     */
    public function getData(): ActionData
    {
        return new ActionData(
            $this->name,
            $this->label,
            $this->icon,
            $this->actionType,
            $this->cssClass,
            $this->routeType,
            $this->route,
            $this->crudController,
            $this->pageType,
            $this->pageId,
            $this->url,
            $this->routeParameters,
            $this->displayIf,
        );
    }
}