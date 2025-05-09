'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    CategoryProductsQueryDocument,
    TypeCategoryProductsQuery,
    TypeCategoryProductsQueryVariables,
} from 'graphql/requests/products/queries/CategoryProductsQuery.ssr';
import { TypeProductOrderingModeEnum, TypeProductFilter } from 'graphql/types';

export async function getCategoryProductsQuery(
    urlSlug: string,
    endCursor: string,
    orderingMode: TypeProductOrderingModeEnum,
    filter: TypeProductFilter | undefined,
    pageSize: number,
) {
    const result = await createQuery<TypeCategoryProductsQuery, TypeCategoryProductsQueryVariables>(
        CategoryProductsQueryDocument,
        {
            urlSlug,
            endCursor,
            orderingMode,
            filter,
            pageSize,
        },
    );

    if (!result.data?.products.edges) {
        return null;
    }

    const productsData = result.data.products.edges.map((edge) => edge?.node);

    const products = productsData.filter((product) => product !== null && product !== undefined);

    return products;
}
