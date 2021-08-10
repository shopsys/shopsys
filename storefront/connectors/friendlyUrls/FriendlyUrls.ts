/* eslint-disable prettier/prettier */
import { productDetailBody, ProductDetailType } from '../products/ProductDetailType';
import { useFetchQuery } from '../../hooks/UseFetchQuery';

export function friendlyUrlQuery(slug: string): string {
    return (
        `query slug {
            slug(slug: "` + slug + `") {
                __typename
                ... on Product {
                    ` + productDetailBody + `
                }
            }
        }`
    );
}

export function getFriendlyUrlResolvedData(slug: string): ProductDetailType | undefined | null {
    const result = useFetchQuery({ query: friendlyUrlQuery(slug) });

    return result?.data?.slug;
}
