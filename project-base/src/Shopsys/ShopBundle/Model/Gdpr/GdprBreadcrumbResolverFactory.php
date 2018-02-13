<?php

namespace Shopsys\ShopBundle\Model\Gdpr;

use Shopsys\ShopBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\ShopBundle\Component\Breadcrumb\BreadcrumbItem;

class GdprBreadcrumbResolverFactory implements BreadcrumbGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        return [
            new BreadcrumbItem('GDPR'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRouteNames()
    {
        return [
            'front_gdpr',
        ];
    }
}
