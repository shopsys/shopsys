'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    BlogCategoryQueryDocument,
    TypeBlogCategoryQuery,
    TypeBlogCategoryQueryVariables,
} from 'graphql/requests/blogCategories/queries/BlogCategoryQuery.ssr';

export async function getBlogCategoryDetailQuery(blogCategorySlug: string) {
    const result = await createQuery<TypeBlogCategoryQuery, TypeBlogCategoryQueryVariables>(BlogCategoryQueryDocument, {
        urlSlug: blogCategorySlug,
    });

    return result.data?.blogCategory;
}
