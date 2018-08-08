<?php

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

class BreadcrumbResolver
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[]
     */
    private $breadcrumbGeneratorsByRouteName;

    public function __construct()
    {
        $this->breadcrumbGeneratorsByRouteName = [];
    }

    public function registerGenerator(BreadcrumbGeneratorInterface $breadcrumbGenerator): void
    {
        foreach ($breadcrumbGenerator->getRouteNames() as $routeName) {
            $this->breadcrumbGeneratorsByRouteName[$routeName] = $breadcrumbGenerator;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function resolveBreadcrumbItems(string $routeName, array $routeParameters = []): array
    {
        if (!$this->hasGeneratorForRoute($routeName)) {
            throw new \Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\BreadcrumbGeneratorNotFoundException($routeName);
        }

        $breadcrumbGenerator = $this->breadcrumbGeneratorsByRouteName[$routeName];

        try {
            return $breadcrumbGenerator->getBreadcrumbItems($routeName, $routeParameters);
        } catch (\Exception $ex) {
            throw new \Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\UnableToGenerateBreadcrumbItemsException($ex);
        }
    }

    public function hasGeneratorForRoute(string $routeName): bool
    {
        return array_key_exists($routeName, $this->breadcrumbGeneratorsByRouteName);
    }
}
