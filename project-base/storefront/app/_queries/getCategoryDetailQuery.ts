'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    CategoryDetailQueryDocument,
    TypeCategoryDetailQuery,
    TypeCategoryDetailQueryVariables,
} from 'graphql/requests/categories/queries/CategoryDetailQuery.ssr';
import { TypeProductFilter, TypeProductOrderingModeEnum } from 'graphql/types';

export async function getCategoryDetailQuery(
    urlSlug: string,
    orderingMode: TypeProductOrderingModeEnum,
    filter: TypeProductFilter | undefined,
) {
    const result = await createQuery<TypeCategoryDetailQuery, TypeCategoryDetailQueryVariables>(
        CategoryDetailQueryDocument,
        {
            urlSlug,
            orderingMode,
            filter,
        },
    );

    return result.data?.category;
}
