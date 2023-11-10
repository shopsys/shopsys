<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use function t;

class StoresBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     */
    public function __construct(
        protected readonly StoreFacade $storeFacade,
    ) {
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return array|\Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        $breadcrumbItems[] = new BreadcrumbItem(
            t('Department stores'),
            'front_stores',
        );

        if (array_key_exists('id', $routeParameters)) {
            $store = $this->storeFacade->getById((int)$routeParameters['id']);

            $breadcrumbItems[] = new BreadcrumbItem(
                $store->getName(),
            );
        }

        return $breadcrumbItems;
    }

    /**
     * @return string[]
     */
    public function getRouteNames(): array
    {
        return ['front_stores_detail', 'front_stores'];
    }
}
