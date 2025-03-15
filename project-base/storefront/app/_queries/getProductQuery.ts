'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ProductDetailQueryDocument,
    TypeProductDetailQuery,
    TypeProductDetailQueryVariables,
} from 'graphql/requests/products/queries/ProductDetailQuery.ssr';
import { redirect } from 'next/navigation';

export const getProductQuery = async (slug: string) => {
    const result = await createQuery<TypeProductDetailQuery, TypeProductDetailQueryVariables>(
        ProductDetailQueryDocument,
        {
            urlSlug: slug,
        },
    );

    if (result.data?.product?.__typename === 'Variant' && result.data.product.mainVariant?.slug) {
        redirect(result.data.product.mainVariant.slug);
    }

    const product =
        result.data?.product?.__typename === 'RegularProduct' || result.data?.product?.__typename === 'MainVariant'
            ? result.data.product
            : undefined;

    return {
        product,
        error: result.error,
    };
};
