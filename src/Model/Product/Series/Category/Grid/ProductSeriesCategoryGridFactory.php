<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category\Grid;

use App\Model\Product\Series\Category\ProductSeriesCategory;
use App\Model\Product\Series\Category\ProductSeriesCategoryRepository;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;

class ProductSeriesCategoryGridFactory implements GridFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryRepository
     */
    private $productSeriesCategoryRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryRepository $productSeriesCategoryRepository
     */
    public function __construct(GridFactory $gridFactory, ProductSeriesCategoryRepository $productSeriesCategoryRepository)
    {
        $this->gridFactory = $gridFactory;
        $this->productSeriesCategoryRepository = $productSeriesCategoryRepository;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->productSeriesCategoryRepository->getAllProductSeriesQueryBuilderByMainDomain();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'psc.id');

        $grid = $this->gridFactory->create('productSeriesCategoryList', $dataSource);
        $grid->enableDragAndDrop(ProductSeriesCategory::class);

        $grid->addColumn('name', 'psct.name', t('Name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_productseriescategory_edit', ['id' => 'psc.id']);
        $grid->addDeleteActionColumn('admin_productseriescategory_delete', ['id' => 'psc.id'])
            ->setConfirmMessage(t('Opravdu chcete odebrat tuto kategorii produktového programu?'));

        $grid->setTheme('Admin/Content/ProductSeriesCategory/listGrid.html.twig');

        return $grid;
    }
}
