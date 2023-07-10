import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import {
    CategoryDetailFragmentApi,
    CategoryProductsQueryApi,
    CategoryProductsQueryDocumentApi,
    CategoryProductsQueryVariablesApi,
} from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { getCategoryOrSeoCategoryGtmProductListName } from 'helpers/gtm/gtm';
import { getMappedProducts } from 'helpers/mappers/products';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { handleQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { RefObject, useEffect, useMemo, useState } from 'react';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { GtmMessageOriginType } from 'types/gtm/enums';
import { useClient } from 'urql';
import { getSlugFromUrl } from 'utils/getSlugFromUrl';

type CategoryDetailProps = {
    category: CategoryDetailFragmentApi;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProps> = ({ category, paginationScrollTargetRef }) => {
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
            <Pagination
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={category.products.totalCount}
            />
        </>
    );
};

const useCategoryProductsData = (): [undefined | CategoryProductsQueryApi, boolean] => {
    const client = useClient();
    const { asPath } = useRouter();
    const { filter, sort, currentPage } = useQueryParams();
    const t = useTypedTranslationFunction();

    const endCursor = getEndCursor(currentPage);
    const urlSlug = getSlugFromUrl(getUrlWithoutGetParameters(asPath));

    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const [categoryDetailData, setCategoryDetailData] = useState<CategoryProductsQueryApi>();
    const [fetching, setFetching] = useState(false);

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            setWasRedirectedToSeoCategory(false);

            return;
        }
        setFetching(true);

        client
            .query<CategoryProductsQueryApi, CategoryProductsQueryVariablesApi>(CategoryProductsQueryDocumentApi, {
                endCursor,
                filter: mapParametersFilter(filter),
                orderingMode: sort ?? null,
                urlSlug,
                pageSize: DEFAULT_PAGE_SIZE,
            })
            .toPromise()
            .then((response) => {
                handleQueryError(response.error, t);
                setCategoryDetailData(response.data ?? undefined);
            })
            .finally(() => setFetching(false));
    }, [urlSlug, sort, JSON.stringify(filter), endCursor]);

    return [categoryDetailData, fetching];
};
