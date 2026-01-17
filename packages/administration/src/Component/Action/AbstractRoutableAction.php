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
     * @return $this
     */
    public function linkToRoute(string $route, array|Closure $parameters = []): self
    {
        $this->actionRoute = new RouteActionRouteData($route, $parameters);

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * Url is provided by string or Closure function that will return string.
     *
     * @param string|\Closure(mixed): string $url
     * @return $this
     */
    public function linkToUrl(Closure|string $url): self
    {
        $this->actionRoute = new UrlActionRouteData($url);

        return $this;
    }

    /**
     * Can be used to generate link to another CRUD controller. This will generate link to the CRUD controller with provided page type.
     * If you are linking to page type that requires entity ID, you must provide callable function that will return entity ID.
     *
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $crudController
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
     * @return $this
     */
    public function setOpenInNewTab(bool $openInNewTab = true): self
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
