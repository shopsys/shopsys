import { CategoryDetailApiType } from '../../components/pages/CategoryDetail/types';
import { categoryDetailBody } from '../categories/CategoryDetail';
import { productDetailBody } from '../products/ProductDetail';
import { ProductDetailType } from '../../components/pages/ProductDetail/types';
import { useFetchQuery } from '../../hooks/UseFetchQuery';

export function friendlyUrlQuery(slug: string): string {
    return `
        query slug {
            slug(slug: "${slug}") {
                __typename
                ... on Product {
                    ${productDetailBody}
                }
                ... on Category {
                    ${categoryDetailBody}
                }
            }
        }
    `;
}

export function getFriendlyUrlResolvedData(slug: string): ProductDetailType | CategoryDetailApiType | undefined | null {
    const result = useFetchQuery({ query: friendlyUrlQuery(slug) });

    return result?.data?.slug;
}
