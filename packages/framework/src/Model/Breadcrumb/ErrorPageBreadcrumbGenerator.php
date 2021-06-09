<?php

namespace Shopsys\FrameworkBundle\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

/**
 * @deprecated Class will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.
 */
class ErrorPageBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct()
    {
        if (static::class === self::class) {
            trigger_error(
                sprintf(
                    'Class "%s" will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.',
                    self::class
                ),
                E_USER_DEPRECATED
            );
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
        trigger_error(
            sprintf(
                'Method "%s" will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return t('Page not found');
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    protected function getTranslatedBreadcrumbForErrorPage(): string
    {
        trigger_error(
            sprintf(
                'Method "%s" will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return t('Oops! Error occurred');
    }
}
