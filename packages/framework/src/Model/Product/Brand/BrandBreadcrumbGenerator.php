<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class BrandBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(protected readonly BrandRepository $brandRepository)
    {
    }

    /**
     * @param string $routeName
     * @param array<string, mixed> $routeParameters
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    #[Override]
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $isBrandDetail = $routeName === 'front_brand_detail';

        $breadcrumbItems[] = new BreadcrumbItem(
            t('Brand overview', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN),
            $isBrandDetail ? 'front_brand_list' : null,
        );

        if ($isBrandDetail) {
            $brand = $this->brandRepository->getById($routeParameters['id']);
            $breadcrumbItems[] = new BreadcrumbItem(
                $brand->getName(),
            );
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRouteNames()
    {
        return ['front_brand_detail'];
    }
}
