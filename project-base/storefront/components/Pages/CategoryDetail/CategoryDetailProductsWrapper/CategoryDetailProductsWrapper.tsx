import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getCategoryOrSeoCategoryGtmProductListName } from 'gtm/utils/getCategoryOrSeoCategoryGtmProductListName';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useMemo } from 'react';

export type CategoryDetailProductsWrapperProps = {
    category: TypeCategoryDetailFragment;
    products: TypeListedProductFragment[] | undefined;
    areProductsFetching: boolean;
    isLoadingMoreProducts: boolean;
    hasNextPage: boolean;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProductsWrapperProps> = ({
    category,
    products,
    areProductsFetching,
    isLoadingMoreProducts,
    hasNextPage,
}) => {
    const { t } = useTranslation();
    const gtmProductListName = useMemo(
        () => getCategoryOrSeoCategoryGtmProductListName(category.originalCategorySlug),
        [category],
    );
    useGtmPaginatedProductListViewEvent(products, gtmProductListName);

    return (
        <>
            <h2 className="sr-only">{t('Products')}</h2>
            <ProductsList
                areProductsFetching={areProductsFetching}
                category={category}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={gtmProductListName}
                isLoadingMoreProducts={isLoadingMoreProducts}
                products={products}
            />
            <Pagination isWithLoadMore hasNextPage={hasNextPage} totalCount={category.products.totalCount} />
        </>
    );
};
