<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Grid;

use App\Model\Product\Series\ProductSeriesRepository;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;

class ProductSeriesGridFactory implements GridFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \App\Model\Product\Series\ProductSeriesRepository
     */
    private $productSeriesRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Product\Series\ProductSeriesRepository $productSeriesRepository
     */
    public function __construct(GridFactory $gridFactory, ProductSeriesRepository $productSeriesRepository)
    {
        $this->gridFactory = $gridFactory;
        $this->productSeriesRepository = $productSeriesRepository;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->productSeriesRepository->getAllProductSeriesQueryBuilderByMainDomain();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ps.id');

        $grid = $this->gridFactory->create('productSeriesList', $dataSource);
        $grid->enablePaging();

        $grid->addColumn('name', 'pst.name', t('Name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_productseries_edit', ['id' => 'ps.id']);
        $grid->addDeleteActionColumn('admin_productseries_delete', ['id' => 'ps.id'])
            ->setConfirmMessage(t('Opravdu chcete odebrat tento produktový program? Pokud je někde použitý, bude deaktivován.'));

        $grid->setTheme('Admin/Content/ProductSeries/listGrid.html.twig');

        return $grid;
    }
}
