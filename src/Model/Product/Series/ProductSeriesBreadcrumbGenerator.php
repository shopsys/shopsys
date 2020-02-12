<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

class ProductSeriesBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesRepository
     */
    private $productSeriesRepository;

    /**
     * @param \App\Model\Product\Series\ProductSeriesRepository $productSeriesRepository
     */
    public function __construct(ProductSeriesRepository $productSeriesRepository)
    {
        $this->productSeriesRepository = $productSeriesRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $path = [
            new BreadcrumbItem(t('Nábytkové programy'), 'front_productseries_list'),
        ];

        if ($routeName === 'front_productseries_list') {
            return $path;
        }

        $productSeries = $this->productSeriesRepository->getById((int)$routeParameters['id']);
        $path[] = new BreadcrumbItem($productSeries->getName(), $routeName, $routeParameters);
        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteNames()
    {
        return ['front_productseries_detail', 'front_productseries_list'];
    }
}
