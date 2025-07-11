<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;

class ProductGridFactory
{
    protected const string PRODUCT_VISIBILITIES_EACH_DOMAIN_CACHE = 'PRODUCT_VISIBILITIES_EACH_DOMAIN_CACHE';
    protected const string PRODUCT_VISIBILITIES_SOME_DOMAIN_CACHE = 'PRODUCT_VISIBILITIES_SOME_DOMAIN_CACHE';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getProductControllerGrid(QueryBuilder $queryBuilder): Grid
    {
        $dataSource = $this->getGridDataSource($queryBuilder);
        $grid = $this->gridFactory->create('productList', $dataSource, 'ROLE_PRODUCT');
        $grid->enablePaging();
        $grid->enableSelecting();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'pt.name', t('Name'), true);
        $grid->addColumn('catnum', 'p.catnum', t('Catalog number'), true);
        $grid->addColumn('price', 'priceForProductList', t('Price'), true)->setClassAttribute('text-right');
        $grid->addColumn('visibility', 'visibility', t('Visibility'))
            ->setClassAttribute('text-center table-col table-col-10');

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_product_edit', ['id' => 'p.id']);
        $grid->addDeleteActionColumn('admin_product_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this product?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Product/listGrid.html.twig', [
            'VARIANT_TYPE_MAIN' => Product::VARIANT_TYPE_MAIN,
            'VARIANT_TYPE_VARIANT' => Product::VARIANT_TYPE_VARIANT,
            'TYPE_INQUIRY' => ProductTypeEnum::TYPE_INQUIRY,
        ]);

        return $grid;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param array $gridViewParameters
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getProductPickerControllerGrid(QueryBuilder $queryBuilder, array $gridViewParameters): Grid
    {
        $dataSource = $this->getGridDataSource($queryBuilder);

        $grid = $this->gridFactory->create('productPicker', $dataSource, 'ROLE_PRODUCT');
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'pt.name', t('Name'), true);
        $grid->addColumn('catnum', 'p.catnum', t('Catalog number'), true);
        $grid->addColumn('visibility', 'visibility', t('Visibility'))
            ->setClassAttribute('table-col table-col-10 text-center');
        $grid->addColumn('select', 'p.id', '')->setClassAttribute('table-col table-col-15 text-center');

        $gridViewParameters['VARIANT_TYPE_MAIN'] = Product::VARIANT_TYPE_MAIN;
        $gridViewParameters['VARIANT_TYPE_VARIANT'] = Product::VARIANT_TYPE_VARIANT;
        $gridViewParameters['TYPE_INQUIRY'] = ProductTypeEnum::TYPE_INQUIRY;
        $grid->setTheme('@ShopsysFramework/Admin/Content/ProductPicker/listGrid.html.twig', $gridViewParameters);

        return $grid;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource
     */
    protected function getGridDataSource(QueryBuilder $queryBuilder): QueryBuilderWithRowManipulatorDataSource
    {
        return new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'p.id',
            function ($product, $products) {
                $product['isVisible'] = $this->isProductVisibleForDefaultPricingGroupOnEachDomain(
                    array_column($products, 'id'),
                    $product['id'],
                );
                $product['isVisibleOnSomeDomain'] = $this->isProductVisibleForDefaultPricingGroupOnSomeDomain(
                    array_column($products, 'id'),
                    $product['id'],
                );

                // actual visibility is rendered in the template, this is just a placeholder for column
                $product['visibility'] = null;

                return $product;
            },
            null,
        );
    }

    /**
     * @param int[] $productIds
     * @param int $productId
     * @return bool
     */
    protected function isProductVisibleForDefaultPricingGroupOnEachDomain(array $productIds, int $productId): bool
    {
        $return = $this->inMemoryCache->getOrSaveValue(
            self::PRODUCT_VISIBILITIES_EACH_DOMAIN_CACHE,
            fn () => $this->productVisibilityFacade->areProductsVisibleForDefaultPricingGroupOnEachDomainIndexedByProductId($productIds),
            self::PRODUCT_VISIBILITIES_EACH_DOMAIN_CACHE,
            ...$productIds,
        );

        return $return[$productId];
    }

    /**
     * @param int[] $productIds
     * @param int $productId
     * @return bool
     */
    protected function isProductVisibleForDefaultPricingGroupOnSomeDomain(array $productIds, int $productId): bool
    {
        $return = $this->inMemoryCache->getOrSaveValue(
            self::PRODUCT_VISIBILITIES_SOME_DOMAIN_CACHE,
            fn () => $this->productVisibilityFacade->areProductsVisibleForDefaultPricingGroupOnSomeDomainIndexedByProductId($productIds),
            self::PRODUCT_VISIBILITIES_SOME_DOMAIN_CACHE,
            ...$productIds,
        );

        return $return[$productId];
    }
}
