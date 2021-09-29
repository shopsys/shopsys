import { mapProductDetailApiData, productDetailBody } from 'connectors/products/ProductDetail';
import { categoryDetailBody } from 'connectors/categories/CategoryDetail';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';
import { useShopsysSelector } from 'redux/store';

export function friendlyUrlQuery(slug: string, categoryDetailSort: string): string {
    const categoryDetailBodyWithSort = categoryDetailBody(categoryDetailSort);

    return `
        query slug {
            slug(slug: "${slug}") {
                __typename
                ... on Product {
                    ${productDetailBody}
                }
                ... on Category {
                    ${categoryDetailBodyWithSort}
                }
            }
        }
    `;
}

export const isProductType = (typename: string): boolean => {
    return ['RegularProduct', 'MainVariant', 'Variant'].includes(typename);
};

export function getFriendlyUrlResolvedData(slug: string): ProductDetailType | CategoryDetailType | undefined | null {
    const categoryDetailSort = useShopsysSelector((state) => state.user.sort);
    const result = useFetchQuery({ query: friendlyUrlQuery(slug, categoryDetailSort) });
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (result?.data?.slug === null || result?.data?.slug === undefined) {
        return undefined;
    }

    if (isProductType(result.data.slug.__typename)) {
        return mapProductDetailApiData(result.data.slug, currentDomainConfig.currencyCode);
    } else if (result.data.slug.__typename === 'Category') {
        return mapCategoryDetailData(result.data.slug, currentDomainConfig.currencyCode);
    }

    return result.data.slug;
}
