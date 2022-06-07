<?php

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

abstract class ErrorPageBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $isPageNotFound = $routeParameters['code'] === '404';
        $breadcrumbName = $isPageNotFound ? $this->getTranslatedBreadcrumbForNotFoundPage() : $this->getTranslatedBreadcrumbForErrorPage();

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

    /**
     * @return string
     */
    abstract protected function getTranslatedBreadcrumbForNotFoundPage(): string;

    /**
     * @return string
     */
    abstract protected function getTranslatedBreadcrumbForErrorPage(): string;
}
