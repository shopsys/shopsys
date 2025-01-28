'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    ArticlesQueryDocument,
    TypeArticlesQuery,
    TypeArticlesQueryVariables,
} from 'graphql/requests/articlesInterface/articles/queries/ArticlesQuery.ssr';

export async function getArticlesQuery(variables: TypeArticlesQueryVariables) {
    const result = await createQuery<TypeArticlesQuery, TypeArticlesQueryVariables>(ArticlesQueryDocument, variables);

    return result.data;
}
