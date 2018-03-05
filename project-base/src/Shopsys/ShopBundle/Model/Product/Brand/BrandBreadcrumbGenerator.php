<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

class BrandBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository
     */
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $isBrandDetail = $routeName === 'front_brand_detail';

        $breadcrumbItems[] = new BreadcrumbItem(
            t('Brand overview'),
            $isBrandDetail ? 'front_brand_list' : null
        );

        if ($isBrandDetail) {
            $brand = $this->brandRepository->getById($routeParameters['id']);
            $breadcrumbItems[] = new BreadcrumbItem(
                $brand->getName()
            );
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteNames()
    {
        return ['front_brand_detail'];
    }
}
