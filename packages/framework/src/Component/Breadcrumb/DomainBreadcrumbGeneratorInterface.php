<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

interface DomainBreadcrumbGeneratorInterface extends BreadcrumbGeneratorInterface
{
    /**
     * @param int $domainId
     * @param string $routeName
     * @param array $routeParameters
     * @param string|null $locale
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItemsOnDomain(
        int $domainId,
        string $routeName,
        array $routeParameters = [],
        ?string $locale = null,
    ): array;
}
