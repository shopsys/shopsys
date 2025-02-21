<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Webmozart\Assert\Assert;

final class Action extends AbstractAction
{
    private ?ActionRouteInterface $actionRoute = null;

    private bool $openInNewTab = false;

    /**
     * @param string $name
     * @param string $label
     * @return self
     */
    public static function create(string $name, string $label): self
    {
        return new self($name, $label);
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

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array or callable function that will return array.
     *
     * @param string $route
     * @param array|\Closure(?object $entity): array $parameters
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
     * @param \Closure(?object $entity): string $url
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
     * @param null|\Closure(?object $entity): int $id
     * @return $this
     */
    public function linkToCrud(string $crudController, ActionType $actionType, ?Closure $id = null): self
    {
        Assert::subclassOf($crudController, AbstractCrudController::class);

        $this->actionRoute = new CrudActionRouteData($crudController, $actionType, $id);

        return $this;
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return '@ShopsysAdministration/crud/inline/action.html.twig';
    }

    /**
     * @param object|null $entity
     * @return array
     */
    protected function getTemplateParameters(?object $entity): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'cssClass' => $this->cssClass,
            'openInNewTab' => $this->openInNewTab,
            'actionRoute' => $this->actionRoute,
            'entity' => $entity,
        ];
    }
}
