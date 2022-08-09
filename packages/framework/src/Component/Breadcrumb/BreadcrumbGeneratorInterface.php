<?php

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

interface BreadcrumbGeneratorInterface
{
    /**
     * @param string $routeName
     * @param array<string, mixed> $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems(string $routeName, array $routeParameters = []): array;

    /**
     * @return string[]
     */
    public function getRouteNames(): array;
}
