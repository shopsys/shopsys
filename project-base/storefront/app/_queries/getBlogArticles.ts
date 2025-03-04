'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    BlogArticlesQueryDocument,
    TypeBlogArticlesQuery,
    TypeBlogArticlesQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticlesQuery.ssr';

export async function getBlogArticles(variables: TypeBlogArticlesQueryVariables) {
    const result = await createQuery<TypeBlogArticlesQuery, TypeBlogArticlesQueryVariables>(
        BlogArticlesQueryDocument,
        variables,
    );

    return result.data;
}
