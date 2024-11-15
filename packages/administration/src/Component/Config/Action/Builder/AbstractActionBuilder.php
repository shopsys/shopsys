<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Shopsys\AdministrationBundle\Component\Config\Action\ActionData;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionRouteType;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionType;
use Shopsys\AdministrationBundle\Component\Config\PageType;

abstract class AbstractActionBuilder
{
    protected ?string $label = null;

    protected ?string $icon = null;

    protected string $cssClass = '';

    /**
     * @var callable|null
     */
    protected $displayIf = null;

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
     * @param string $label
     * @return $this
     */
    abstract public static function create(string $name, string $label): self;

    /**
     * @param string $name
     * @param string $label
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     */
    protected function __construct(protected string $name, string $label, protected ActionType $actionType)
    {
        $this->label = $label;
    }

    /**
     * Set name of action that will be shown to the users
     *
     * @param string $label
     * @return $this
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
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

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
     * @param string $route
     * @param array|callable $parameters
     */
    protected function setRouteParameters(string $route, array|callable $parameters): void
    {
        $this->routeType = ActionRouteType::ROUTE;
        $this->route = $route;
        $this->routeParameters = $parameters;
    }

    /**
     * @param callable|string $url
     */
    protected function setLinkParameters(callable|string $url): void
    {
        $this->routeType = ActionRouteType::URL;
        $this->url = $url;
    }

    /**
     * @param string $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param callable|null $id
     */
    protected function setCrudParameters(string $crudController, PageType $pageType, ?callable $id): void
    {
        $this->routeType = ActionRouteType::CRUD;

        $this->crudController = $crudController;
        $this->pageType = $pageType;
        $this->pageId = $id;
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
