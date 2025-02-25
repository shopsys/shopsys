<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use Closure;
use Shopsys\AdministrationBundle\Component\Action\RouteData\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Action\RouteData\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Webmozart\Assert\Assert;

trait ActionRouteTrait
{
    private ?ActionRouteInterface $actionRoute = null;

    private bool $openInNewTab = false;

    /**
     * @var array<string, ?string>
     */
    protected array $actionRouteForbiddenAttributes = [
        'href' => 'Use one of the "linkTo*" methods to generate href link instead',
        'target' => 'Use `setOpenInNewTab` method to open link in new tab',
    ];

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array or Closure function that will return array.
     *
     * @param string $route
     * @param array|\Closure(mixed): array $parameters
     * @return $this
     */
    public function linkToRoute(string $route, array|Closure $parameters = []): self
    {
        $this->actionRoute = new RouteActionRouteData($route, $parameters);

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * Url is provided by a callable function that will return string.
     *
     * @param \Closure(mixed): string $url
     * @return $this
     */
    public function linkToUrl(Closure $url): self
    {
        $this->actionRoute = new UrlActionRouteData($url);

        return $this;
    }

    /**
     * Can be used to generate link to another CRUD controller. This will generate link to the CRUD controller with provided page type.
     * If you are linking to page type that requires entity ID, you must provide callable function that will return entity ID.
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param null|\Closure(mixed): int $id
     * @return $this
     */
    public function linkToCrud(string $crudController, ActionType $actionType, ?Closure $id = null): self
    {
        Assert::subclassOf($crudController, AbstractCrudController::class);

        $this->actionRoute = new CrudActionRouteData($crudController, $actionType, $id);

        return $this;
    }

    /**
     * Determines if new tab should be opened when action is clicked
     *
     * @param bool $openInNewTab
     * @return $this
     */
    public function setOpenInNewTab(bool $openInNewTab = true): self
    {
        $this->openInNewTab = $openInNewTab;

        return $this;
    }
}
