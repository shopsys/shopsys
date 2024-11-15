<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use ReflectionFunction;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionType;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Webmozart\Assert\Assert;

final class DatagridAction extends AbstractActionBuilder
{
    /**
     * @param string $name
     * @param string $label
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\Builder\DatagridAction
     */
    public static function create(string $name, string $label): self
    {
        return new self($name, $label, ActionType::DATAGRID);
    }

    /**
     * Set function that will determine if action should be displayed
     *
     * @param callable(): bool $function Function must return boolean value. If function returns false, action will not be displayed
     * @return $this
     */
    public function displayIf(callable $function): self
    {
        $reflectionFunction = new ReflectionFunction($function);

        Assert::same($reflectionFunction->getNumberOfParameters(), 0, 'Datagrid action does not support `displayIf` function with parameters');

        $this->displayIf = $function;

        return $this;
    }

    /**
     * Can be used to generate link to another CRUD controller. This will generate link to the CRUD controller with provided page type.
     *
     * Datagrid will generate id by itself.
     *
     * @param string $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return $this
     */
    public function linkToCrud(string $crudController, PageType $pageType): self
    {
        $this->setCrudParameters($crudController, $pageType, null);

        return $this;
    }

    /**
     * Can be used to generate link as URL. That can be used if you want to link to external URL.
     * URL can be provided as string.
     *
     * @param string $url
     * @return $this
     */
    public function linkToUrl(string $url): self
    {
        $this->setLinkParameters($url);

        return $this;
    }

    /**
     * Can be used to generate link to another route in the application.
     * Parameters can be passed as array.
     * Datagrid will automatically add id parameter to the route.
     *
     * @param string $route
     * @param array $parameters
     * @return $this
     */
    public function linkToRoute(string $route, array $parameters = []): self
    {
        $this->setRouteParameters($route, $parameters);

        return $this;
    }
}
