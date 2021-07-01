<?php

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;

/**
 * @deprecated Class will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.
 */
class ErrorPageBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct()
    {
        if (static::class === self::class) {
            DeprecationHelper::triggerAbstractClass(self::class);
        }
    }

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
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    protected function getTranslatedBreadcrumbForNotFoundPage(): string
    {
        DeprecationHelper::triggerAbstractMethod(__METHOD__);

        return t('Page not found');
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    protected function getTranslatedBreadcrumbForErrorPage(): string
    {
        DeprecationHelper::triggerAbstractMethod(__METHOD__);

        return t('Oops! Error occurred');
    }
}
