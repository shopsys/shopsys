import { CategoryDetailContentMessage } from './CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { CategoryDetailFragmentApi, useCategoryProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getCategoryOrSeoCategoryGtmProductListName } from 'helpers/gtm/gtm';
import { getMappedProducts } from 'helpers/mappers/products';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { RefObject, useMemo } from 'react';
import { GtmMessageOriginType } from 'types/gtm/enums';

type CategoryDetailProps = {
    category: CategoryDetailFragmentApi;
    containerWrapRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProps> = ({ category, containerWrapRef }) => {
    const { query } = useRouter();
    const { currentPage } = useQueryParams();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query.sort));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query.filter));

    const [{ data: categoryProductsData, fetching }] = useQueryError(
        useCategoryProductsQueryApi({
            variables: {
                endCursor: getEndCursor(currentPage),
                filter: mapParametersFilter(parametersFilter),
                orderingMode,
                uuid: category.uuid,
                pageSize: DEFAULT_PAGE_SIZE,
            },
        }),
    );

    const gtmProductListName = useMemo(
        () => getCategoryOrSeoCategoryGtmProductListName(category.originalCategorySlug),
        [category],
    );

    const categoryListedProducts = getMappedProducts(categoryProductsData?.category?.products.edges);

    useGtmPaginatedProductListViewEvent(categoryListedProducts, gtmProductListName);

    return (
        <>
            {categoryListedProducts && categoryListedProducts.length !== 0 ? (
                <>
                    <ProductsList
                        gtmProductListName={gtmProductListName}
                        products={categoryListedProducts}
                        fetching={fetching}
                        category={category}
                        gtmMessageOrigin={GtmMessageOriginType.other}
                    />
                    <Pagination containerWrapRef={containerWrapRef} totalCount={category.products.totalCount} />
                </>
            ) : (
                <CategoryDetailContentMessage />
            )}
        </>
    );
};
