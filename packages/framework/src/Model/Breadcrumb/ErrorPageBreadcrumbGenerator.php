<?php

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

/**
 * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem
 */
class ErrorPageBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $isPageNotFound = $routeParameters['code'] === '404';
        $breadcrumbName = $isPageNotFound ? t('Page not found') : t('Oops! Error occurred');

        return [
            new BreadcrumbItem($breadcrumbName),
        ];
    }

    /**
     * @return string[]
     */
    public function getRouteNames()
    {
        return [
            'front_error_page',
            'front_error_page_format',
        ];
    }
}
