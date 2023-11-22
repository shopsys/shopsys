<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

class BreadcrumbItem
{
    protected string $name;

    protected ?string $routeName = null;

    /**
     * @param string $name
     * @param string|null $routeName
     * @param mixed[] $routeParameters
     */
    public function __construct(string $name, ?string $routeName = null, protected readonly array $routeParameters = [])
    {
        $this->name = $name;
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    /**
     * @return mixed[]
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }
}
