'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ProductsByCatnumsDocument,
    TypeProductsByCatnums,
    TypeProductsByCatnumsVariables,
} from 'graphql/requests/products/queries/ProductsByCatnumsQuery.ssr';

export async function getLastVisitedProductsQuery(productsCatnums: string[]) {
    const result = await createQuery<TypeProductsByCatnums, TypeProductsByCatnumsVariables>(ProductsByCatnumsDocument, {
        catnums: productsCatnums,
    });

    return result;
}
