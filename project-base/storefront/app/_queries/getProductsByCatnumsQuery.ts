'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ProductsByCatnumsDocument,
    TypeProductsByCatnums,
    TypeProductsByCatnumsVariables,
} from 'graphql/requests/products/queries/ProductsByCatnumsQuery.ssr';

export const getProductsByCatnumsQuery = async (catnums: string[]) => {
    const productListResult = await createQuery<TypeProductsByCatnums, TypeProductsByCatnumsVariables>(
        ProductsByCatnumsDocument,
        {
            catnums,
        },
    );

    return productListResult.data?.productsByCatnums ?? [];
};
