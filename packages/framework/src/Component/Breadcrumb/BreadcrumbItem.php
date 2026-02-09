<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

class BreadcrumbItem
{
    public function __construct(
        protected string $name,
        protected ?string $routeName = null,
        protected readonly array $routeParameters = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }
}
