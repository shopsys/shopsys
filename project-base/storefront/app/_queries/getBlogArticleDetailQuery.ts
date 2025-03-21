'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    BlogArticleDetailQueryDocument,
    TypeBlogArticleDetailQuery,
    TypeBlogArticleDetailQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticleDetailQuery.ssr';

export async function getBlogArticleDetailQuery(blogArticleSlug: string) {
    const result = await createQuery<TypeBlogArticleDetailQuery, TypeBlogArticleDetailQueryVariables>(
        BlogArticleDetailQueryDocument,
        {
            urlSlug: blogArticleSlug,
        },
    );

    return result.data?.blogArticle;
}
