<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

abstract class SimpleBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @var string[]|null
     */
    protected ?array $routeNameMap = null;

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $routeNameMap = $this->getRouteNameMap();

        return [
            new BreadcrumbItem($routeNameMap[$routeName]),
        ];
    }

    /**
     * @return string[]
     */
    public function getRouteNames()
    {
        return array_keys($this->getRouteNameMap());
    }

    /**
     * @return string[]
     */
    protected function getRouteNameMap()
    {
        if ($this->routeNameMap === null) {
            // Caching in order to translate breadcrumb item names only once
            $this->routeNameMap = $this->getTranslatedBreadcrumbsByRouteNames();
        }

        return $this->routeNameMap;
    }

    /**
     * @return array<string, string>
     */
    abstract protected function getTranslatedBreadcrumbsByRouteNames(): array;
}
