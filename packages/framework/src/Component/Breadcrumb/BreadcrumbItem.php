<?php

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

class BreadcrumbItem
{
    protected string $name;

    protected ?string $routeName = null;

    /**
     * @param string $name
     * @param string|null $routeName
     * @param array $routeParameters
     */
    public function __construct($name, $routeName = null, protected readonly array $routeParameters = [])
    {
        $this->name = $name;
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }
}
