<?php

declare(strict_types=1);


namespace App\Model\Stock;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

class StockBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @var \App\Model\Stock\StockFacade
     */
    private StockFacade $stockFacade;

    /**
     * @param \App\Model\Stock\StockFacade $stockFacade
     */
    public function __construct(StockFacade $stockFacade)
    {
        $this->stockFacade = $stockFacade;
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return array|\Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        $breadcrumbItems[] = new BreadcrumbItem(
            t('Prodejny'),
            'front_stores'
        );

        if (array_key_exists('id', $routeParameters)) {
            $store = $this->stockFacade->getById((int)$routeParameters['id']);

            $breadcrumbItems[] = new BreadcrumbItem(
                $store->getName()
            );
        }

        return $breadcrumbItems;
    }

    /**
     * @return array|string[]
     */
    public function getRouteNames(): array
    {
        return ['front_stores_detail', 'front_stores'];
    }
}
