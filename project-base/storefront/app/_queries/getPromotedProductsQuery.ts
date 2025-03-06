'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    PromotedProductsQueryDocument,
    TypePromotedProductsQuery,
    TypePromotedProductsQueryVariables,
} from 'graphql/requests/products/queries/PromotedProductsQuery.ssr';

export const getPromotedProductsQuery = async () => {
    const result = await createQuery<TypePromotedProductsQuery, TypePromotedProductsQueryVariables>(
        PromotedProductsQueryDocument,
        {},
    );

    return result.data;
};
