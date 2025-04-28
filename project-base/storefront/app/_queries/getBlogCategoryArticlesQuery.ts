'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    BlogCategoryArticlesDocument,
    TypeBlogCategoryArticles,
    TypeBlogCategoryArticlesVariables,
} from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.ssr';
import { cache } from 'react';

export const getBlogCategoryArticlesQuery = cache(async (uuid: string, endCursor: string, pageSize: number) => {
    const result = await createQuery<TypeBlogCategoryArticles, TypeBlogCategoryArticlesVariables>(
        BlogCategoryArticlesDocument,
        {
            uuid,
            endCursor,
            pageSize,
        },
    );

    return result.data?.blogCategory?.blogArticles;
});
