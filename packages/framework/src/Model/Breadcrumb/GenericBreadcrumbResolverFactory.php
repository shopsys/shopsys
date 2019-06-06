<?php

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver;

class GenericBreadcrumbResolverFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[]
     */
    protected $breadcrumbGenerators;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[] $breadcrumbGenerators
     */
    public function __construct(iterable $breadcrumbGenerators) {
        $this->breadcrumbGenerators = $breadcrumbGenerators;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver
     */
    public function create()
    {
        $frontBreadcrumbResolver = new BreadcrumbResolver();
        foreach ($this->breadcrumbGenerators as $breadcrumbGenerator) {
            $frontBreadcrumbResolver->registerGenerator($breadcrumbGenerator);
        }

        return $frontBreadcrumbResolver;
    }
}
