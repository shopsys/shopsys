import { CategoryDetailProductsWrapperProps, CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { CategoryDetailProductsWrapperPlaceholder } from './CategoryDetailProductsWrapperPlaceholder';
import { CategoryProductsQueryDocument } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { useSessionStore } from 'store/useSessionStore';
import { useProductsData } from 'utils/loadMore/useProductsData';
import { getMappedProducts } from 'utils/mappers/products';
import { useDeferredRender } from 'utils/useDeferredRender';

type DeferredCategoryDetailProductsWrapperProps = Pick<
    CategoryDetailProductsWrapperProps,
    'category' | 'paginationScrollTargetRef'
>;

export const DeferredCategoryDetailProductsWrapper: FC<DeferredCategoryDetailProductsWrapperProps> = ({
    category,
    paginationScrollTargetRef,
}) => {
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
    const products = getMappedProducts(categoryProductsData);
    const shouldRender = useDeferredRender('product_list');

    return shouldRender ? (
        <CategoryDetailProductsWrapper
            category={category}
            fetching={fetching}
            hasNextPage={hasNextPage}
            loadMoreFetching={loadMoreFetching}
            paginationScrollTargetRef={paginationScrollTargetRef}
            products={products}
        />
    ) : (
        <CategoryDetailProductsWrapperPlaceholder category={category} products={products} />
    );
};
