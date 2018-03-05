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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface $breadcrumbGenerator
     */
    public function registerGenerator(BreadcrumbGeneratorInterface $breadcrumbGenerator)
    {
        foreach ($breadcrumbGenerator->getRouteNames() as $routeName) {
            $this->breadcrumbGeneratorsByRouteName[$routeName] = $breadcrumbGenerator;
        }
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function resolveBreadcrumbItems($routeName, array $routeParameters = [])
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

    /**
     * @param string $routeName
     * @return bool
     */
    public function hasGeneratorForRoute($routeName)
    {
        return array_key_exists($routeName, $this->breadcrumbGeneratorsByRouteName);
    }
}
