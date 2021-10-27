import { mapProductDetailApiData, productDetailBody } from 'connectors/products/ProductDetail';
import { mapStoreDetailApiData, storeDetailBody } from 'connectors/stores/StoreDetail';
import { categoryDetailBody } from 'connectors/categories/CategoryDetail';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { StoreDetailType } from 'connectors/stores/types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';
import { useShopsysSelector } from 'redux/main';

export function friendlyUrlQuery(slug: string, categoryDetailSort: string, endCursorForPagination: string): string {
    const categoryDetailBodyWithSortAndPagination = categoryDetailBody(categoryDetailSort, endCursorForPagination);

    return `
        query slug {
            slug(slug: "${slug}") {
                __typename
                ... on Product {
                    ${productDetailBody}
                }
                ... on Category {
                    ${categoryDetailBodyWithSortAndPagination}
                }
                ... on Store {
                    ${storeDetailBody}
                }
            }
        }
    `;
}

export const isProductType = (typename: string): boolean => {
    return ['RegularProduct', 'MainVariant', 'Variant'].includes(typename);
};

export function getFriendlyUrlResolvedData(
    slug: string,
): ProductDetailType | CategoryDetailType | StoreDetailType | undefined | null {
    const categoryDetailSort = useShopsysSelector((state) => state.user.sort);
    const endCursorForPagination = useShopsysSelector((state) => state.user.pagination.paginationCursor);
    const result = useFetchQuery({ query: friendlyUrlQuery(slug, categoryDetailSort, endCursorForPagination) });
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (result?.data?.slug === null || result?.data?.slug === undefined) {
        return undefined;
    }

    if (isProductType(result.data.slug.__typename)) {
        return mapProductDetailApiData(result.data.slug, currentDomainConfig.currencyCode);
    } else if (result.data.slug.__typename === 'Category') {
        return mapCategoryDetailData(result.data.slug, currentDomainConfig.currencyCode);
    } else if (result.data.slug.__typename === 'Store') {
        return mapStoreDetailApiData(result.data.slug);
    }

    return result.data.slug;
}
