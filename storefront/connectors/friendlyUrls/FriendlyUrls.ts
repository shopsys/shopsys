import { productDetailBody } from '../products/ProductDetailType';
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
            }
        }
    `;
}

export function getFriendlyUrlResolvedData(slug: string): ProductDetailType | undefined | null {
    const result = useFetchQuery({ query: friendlyUrlQuery(slug) });

    return result?.data?.slug;
}
