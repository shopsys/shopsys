<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

interface BreadcrumbGeneratorInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems(string $routeName, array $routeParameters = []): array;

    /**
     * @return string[]
     */
    public function getRouteNames(): array;
}
