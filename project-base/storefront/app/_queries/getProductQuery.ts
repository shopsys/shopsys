'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ProductDetailQueryDocument,
    TypeProductDetailQuery,
    TypeProductDetailQueryVariables,
} from 'graphql/requests/products/queries/ProductDetailQuery.ssr';
import { headers } from 'next/headers';

export async function getProductQuery() {
    const headersList = headers();
    const slug = headersList.get('x-friendly-slug');

    const result = await createQuery<TypeProductDetailQuery, TypeProductDetailQueryVariables>(
        ProductDetailQueryDocument,
        {
            urlSlug: slug,
        },
    );

    return result;
}
