import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { CategoryDetailFragmentApi, CategoryProductsQueryDocumentApi } from 'graphql/generated';
import { getCategoryOrSeoCategoryGtmProductListName } from 'gtm/helpers/gtm';
import { getMappedProducts } from 'helpers/mappers/products';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { RefObject, useMemo } from 'react';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { useSessionStore } from 'store/useSessionStore';
import { useProductsData } from 'helpers/pagination/loadMore';

type CategoryDetailProps = {
    category: CategoryDetailFragmentApi;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProps> = ({ category, paginationScrollTargetRef }) => {
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const [categoryProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        CategoryProductsQueryDocumentApi,
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
                gtmProductListName={gtmProductListName}
                products={categoryListedProducts}
                fetching={fetching}
                loadMoreFetching={loadMoreFetching}
                category={category}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
            <Pagination
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={category.products.totalCount}
                isWithLoadMore
                hasNextPage={hasNextPage}
            />
        </>
    );
};
