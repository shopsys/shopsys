<?php

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver;
use Shopsys\FrameworkBundle\Model\Article\ArticleBreadcrumbGenerator;
use Shopsys\FrameworkBundle\Model\Category\CategoryBreadcrumbGenerator;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataBreadcrumbGenerator;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandBreadcrumbGenerator;
use Shopsys\FrameworkBundle\Model\Product\ProductBreadcrumbGenerator;

/**
 * @deprecated Using this class is deprecated since SSFW 7.3, use GenericBreadcrumbResolverFactory instead which is easier to use
 */
class FrontBreadcrumbResolverFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface[]
     */
    protected $breadcrumbGenerators;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleBreadcrumbGenerator $articleBreadcrumbGenerator
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryBreadcrumbGenerator $categoryBreadcrumbGenerator
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductBreadcrumbGenerator $productBreadcrumbGenerator
     * @param \Shopsys\FrameworkBundle\Model\Breadcrumb\SimpleBreadcrumbGenerator $frontBreadcrumbGenerator
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandBreadcrumbGenerator $brandBreadcrumbGenerator
     * @param \Shopsys\FrameworkBundle\Model\Breadcrumb\ErrorPageBreadcrumbGenerator $errorPageBreadcrumbGenerator
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataBreadcrumbGenerator $personalDataBreadcrumbGenerator
     */
    public function __construct(
        ArticleBreadcrumbGenerator $articleBreadcrumbGenerator,
        CategoryBreadcrumbGenerator $categoryBreadcrumbGenerator,
        ProductBreadcrumbGenerator $productBreadcrumbGenerator,
        SimpleBreadcrumbGenerator $frontBreadcrumbGenerator,
        BrandBreadcrumbGenerator $brandBreadcrumbGenerator,
        ErrorPageBreadcrumbGenerator $errorPageBreadcrumbGenerator,
        PersonalDataBreadcrumbGenerator $personalDataBreadcrumbGenerator
    ) {
        @trigger_error(
            sprintf('Using "%s" is deprecated since SSFW 7.3, use "%s" instead which is easier to use', self::class, GenericBreadcrumbResolverFactory::class),
            E_USER_DEPRECATED
        );

        $this->breadcrumbGenerators = [
            $articleBreadcrumbGenerator,
            $categoryBreadcrumbGenerator,
            $productBreadcrumbGenerator,
            $frontBreadcrumbGenerator,
            $brandBreadcrumbGenerator,
            $errorPageBreadcrumbGenerator,
            $personalDataBreadcrumbGenerator,
        ];
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
