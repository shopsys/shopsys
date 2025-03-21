'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    BlogCategoriesDocument,
    TypeBlogCategories,
    TypeBlogCategoriesVariables,
} from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.ssr';

export async function getBlogCategoriesQuery() {
    const result = await createQuery<TypeBlogCategories, TypeBlogCategoriesVariables>(BlogCategoriesDocument, {});

    return result.data?.blogCategories;
}
