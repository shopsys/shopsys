import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import {
    CategoryDetailFragmentApi,
    CategoryProductsQueryApi,
    CategoryProductsQueryDocumentApi,
    CategoryProductsQueryVariablesApi,
} from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getCategoryOrSeoCategoryGtmProductListName } from 'helpers/gtm/gtm';
import { getMappedProducts } from 'helpers/mappers/products';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { handleQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';
import router, { useRouter } from 'next/router';
import { RefObject, useEffect, useMemo, useState } from 'react';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { GtmMessageOriginType } from 'types/gtm/enums';
import { useClient } from 'urql';
import { getSlugFromUrl } from 'utils/getSlugFromUrl';

type CategoryDetailProps = {
    category: CategoryDetailFragmentApi;
    containerWrapRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProps> = ({ category, containerWrapRef }) => {
    const [categoryProductsData, fetching] = useCategoryProductsData();

    const gtmProductListName = useMemo(
        () => getCategoryOrSeoCategoryGtmProductListName(category.originalCategorySlug),
        [category],
    );

    const categoryListedProducts = getMappedProducts(categoryProductsData?.products.edges);

    useGtmPaginatedProductListViewEvent(categoryListedProducts, gtmProductListName);

    return (
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
    );
};

const useCategoryProductsData = (): [undefined | CategoryProductsQueryApi, boolean] => {
    const client = useClient();
    const { query, asPath } = useRouter();
    const { currentPage } = useQueryParams();
    const t = useTypedTranslationFunction();

    const endCursor = getEndCursor(currentPage);
    const filter = mapParametersFilter(getFilterOptions(parseFilterOptionsFromQuery(query.filter)));
    const orderingMode = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));
    const urlSlug = getSlugFromUrl(getUrlWithoutGetParameters(asPath));

    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const [categoryDetailData, setCategoryDetailData] = useState<undefined | CategoryProductsQueryApi>(undefined);
    const [fetching, setFetching] = useState<boolean>(false);

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            setWasRedirectedToSeoCategory(false);

            return;
        }
        setFetching(true);

        client
            .query<CategoryProductsQueryApi, CategoryProductsQueryVariablesApi>(CategoryProductsQueryDocumentApi, {
                endCursor,
                filter,
                orderingMode,
                urlSlug,
                pageSize: DEFAULT_PAGE_SIZE,
            })
            .toPromise()
            .then((response) => {
                handleQueryError(response.error, t);
                setCategoryDetailData(response.data ?? undefined);
            })
            .finally(() => setFetching(false));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [urlSlug, orderingMode, JSON.stringify(filter)]);

    return [categoryDetailData, fetching];
};
