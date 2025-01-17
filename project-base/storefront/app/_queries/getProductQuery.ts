'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ProductDetailQueryDocument,
    TypeProductDetailQuery,
    TypeProductDetailQueryVariables,
} from 'graphql/requests/products/queries/ProductDetailQuery.ssr';
import { headers } from 'next/headers';

export const getProductQuery = async () => {
    const headersList = headers();
    const slug = headersList.get('x-friendly-slug');

    const result = await createQuery<TypeProductDetailQuery, TypeProductDetailQueryVariables>(
        ProductDetailQueryDocument,
        {
            urlSlug: slug,
        },
    );

    const product =
        result.data?.product?.__typename === 'RegularProduct' || result.data?.product?.__typename === 'MainVariant'
            ? result.data.product
            : undefined;

    return {
        product,
        error: result.error,
    };
};
