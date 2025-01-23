'use server';

import { createQuery } from 'app/_urql/urql-dto';
import { ProductListQueryDocument } from 'graphql/requests/productLists/queries/ProductListQuery.ssr';
import { TypeProductListQueryVariables } from 'graphql/requests/productLists/queries/ProductListQuery.ssr';
import { TypeProductListQuery } from 'graphql/requests/productLists/queries/ProductListQuery.ssr';
import { TypeProductListTypeEnum } from 'graphql/types';

export const getProductListProductsQuery = async (uuid: string, listType: TypeProductListTypeEnum) => {
    const productListResult = await createQuery<TypeProductListQuery, TypeProductListQueryVariables>(
        ProductListQueryDocument,
        {
            input: {
                uuid: uuid,
                type: listType,
            },
        },
    );

    return productListResult.data?.productList?.products ?? [];
};
