import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getCategoryOrSeoCategoryGtmProductListName } from 'gtm/utils/getCategoryOrSeoCategoryGtmProductListName';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import { RefObject, useMemo } from 'react';

export type CategoryDetailProductsWrapperProps = {
    category: TypeCategoryDetailFragment;
    products: TypeListedProductFragment[] | undefined;
    fetching: boolean;
    loadMoreFetching: boolean;
    hasNextPage: boolean;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProductsWrapperProps> = ({
    category,
    products,
    fetching,
    loadMoreFetching,
    hasNextPage,
    paginationScrollTargetRef,
}) => {
    const gtmProductListName = useMemo(
        () => getCategoryOrSeoCategoryGtmProductListName(category.originalCategorySlug),
        [category],
    );
    useGtmPaginatedProductListViewEvent(products, gtmProductListName);

    return (
        <>
            <ProductsList
                category={category}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={gtmProductListName}
                isFetching={fetching}
                isLoadMoreFetching={loadMoreFetching}
                products={products}
            />
            <Pagination
                isWithLoadMore
                hasNextPage={hasNextPage}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={category.products.totalCount}
            />
        </>
    );
};
