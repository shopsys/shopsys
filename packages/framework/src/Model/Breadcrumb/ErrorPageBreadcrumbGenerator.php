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
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems(string $routeName, array $routeParameters = []): array
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
    public function getRouteNames(): array
    {
        return [
            'front_error_page',
            'front_error_page_format',
        ];
    }
}
