<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use Closure;
use Override;
use Shopsys\AdministrationBundle\Component\Action\RouteData\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Action\RouteData\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Webmozart\Assert\Assert;

abstract class AbstractRoutableAction extends AbstractAction
{
    protected ?ActionRouteInterface $actionRoute = null;

    private bool $openInNewTab = false;

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array or Closure function that will return array.
     *
     * @param string $route #Route
     * @param array|\Closure(mixed): array $parameters
     */
    public function linkToRoute(string $route, array|Closure $parameters = []): static
    {
        $this->actionRoute = new RouteActionRouteData($route, $parameters);

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * Url is provided by string or Closure function that will return string.
     *
     * @param string|\Closure(mixed): string $url
     */
    public function linkToUrl(Closure|string $url): static
    {
        $this->actionRoute = new UrlActionRouteData($url);

        return $this;
    }

    /**
     * Can be used to generate link to an action of a CRUD controller, either a built-in one (ActionType) or a custom one (by its name).
     * If the action works with a single record, the closure returns the entity ID, an action with more route parameters
     * gets them as an array from the closure. Linking to an unknown action fails when the action is rendered, links to disabled actions are hidden.
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
     * @param null|\Closure(mixed): (int|array<string, mixed>) $parameters
     */
    public function linkToCrud(string $crudController, ActionType|string $action, ?Closure $parameters = null): static
    {
        Assert::subclassOf($crudController, AbstractCrudController::class);

        $this->actionRoute = new CrudActionRouteData($crudController, $action, $parameters);

        return $this;
    }

    /**
     * Determines if new tab should be opened when action is clicked
     */
    public function setOpenInNewTab(bool $openInNewTab = true): static
    {
        $this->openInNewTab = $openInNewTab;

        return $this;
    }

    protected function prepareRoutableAttributes(): void
    {
        if ($this->openInNewTab === true) {
            $this->attributes['target'] = '_blank';
        }
    }

    /**
     * Forbidden attributes that can not be set by user. If user tries to set them, exception will be thrown.
     * Key is attribute name, value is message that will be shown in exception.
     *
     * @return array<string, string|null>
     */
    #[Override]
    protected function getForbiddenAttributes(): array
    {
        return [
            'href' => 'Use one of the "linkTo*" methods to generate href link instead',
            'target' => 'Use `setOpenInNewTab` method to open link in new tab',
        ];
    }
}
