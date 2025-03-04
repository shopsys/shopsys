'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    PromotedCategoriesQueryDocument,
    TypePromotedCategoriesQuery,
    TypePromotedCategoriesQueryVariables,
} from 'graphql/requests/categories/queries/PromotedCategoriesQuery.ssr';

export async function getPromotedCategories() {
    const result = await createQuery<TypePromotedCategoriesQuery, TypePromotedCategoriesQueryVariables>(
        PromotedCategoriesQueryDocument,
        {},
    );

    return result.data;
}
