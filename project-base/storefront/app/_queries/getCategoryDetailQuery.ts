'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    CategoryDetailQueryDocument,
    TypeCategoryDetailQuery,
    TypeCategoryDetailQueryVariables,
} from 'graphql/requests/categories/queries/CategoryDetailQuery.ssr';
import { TypeProductOrderingModeEnum, TypeProductFilter } from 'graphql/types';
import { wait } from 'utils/wait';

export async function getCategoryDetailQuery(
    urlSlug: string,
    orderingMode: TypeProductOrderingModeEnum,
    filter: TypeProductFilter | undefined,
) {
    await wait(5000);

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
