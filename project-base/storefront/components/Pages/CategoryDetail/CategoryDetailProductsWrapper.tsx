import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { CategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { CategoryProductsQueryDocument } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { getCategoryOrSeoCategoryGtmProductListName } from 'gtm/helpers/gtm';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { useProductsData } from 'helpers/loadMore';
import { getMappedProducts } from 'helpers/mappers/products';
import { RefObject, useMemo } from 'react';
import { useSessionStore } from 'store/useSessionStore';

type CategoryDetailProps = {
    category: CategoryDetailFragment;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProps> = ({ category, paginationScrollTargetRef }) => {
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const [categoryProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        CategoryProductsQueryDocument,
        category.products.totalCount,
        {
            shouldAbortFetchingProducts: wasRedirectedToSeoCategory,
            abortedFetchCallback: () => setWasRedirectedToSeoCategory(false),
        },
    );
    const categoryListedProducts = getMappedProducts(categoryProductsData);

    const gtmProductListName = useMemo(
        () => getCategoryOrSeoCategoryGtmProductListName(category.originalCategorySlug),
        [category],
    );
    useGtmPaginatedProductListViewEvent(categoryListedProducts, gtmProductListName);

    return (
        <>
            <ProductsList
                category={category}
                fetching={fetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={gtmProductListName}
                loadMoreFetching={loadMoreFetching}
                products={categoryListedProducts}
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
