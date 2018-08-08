<?php

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

class BreadcrumbItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * @param string $name
     * @param string|null $routeName
     */
    public function __construct($name, $routeName = null, array $routeParameters = [])
    {
        $this->name = $name;
        $this->routeName = $routeName;
        $this->routeParameters = $routeParameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }
    
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }
}
