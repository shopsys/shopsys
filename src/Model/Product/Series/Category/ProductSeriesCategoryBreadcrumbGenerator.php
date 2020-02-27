<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;

class ProductSeriesCategoryBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryRepository
     */
    private $productSeriesCategoryRepository;

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryRepository $productSeriesCategoryRepository
     */
    public function __construct(ProductSeriesCategoryRepository $productSeriesCategoryRepository)
    {
        $this->productSeriesCategoryRepository = $productSeriesCategoryRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        $path = [
            new BreadcrumbItem(t('Nábytkové programy'), 'front_productseries_list'),
        ];

        $productSeriesCategory = $this->productSeriesCategoryRepository->getById((int)$routeParameters['id']);
        $path[] = new BreadcrumbItem($productSeriesCategory->getName(), $routeName, $routeParameters);

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteNames(): array
    {
        return ['front_productseriescategory_detail'];
    }
}
