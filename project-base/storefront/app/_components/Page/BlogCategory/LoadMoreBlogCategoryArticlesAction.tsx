'use server';

import { BlogArticlesServer } from './BlogArticlesServer';
import { getBlogCategoryArticlesQuery } from 'app/_queries/getBlogCategoryArticlesQuery';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';

// ❗ WARNING: This file uses an unconventional pattern for server actions.
// This is a hack to return TSX directly from a server action, which is not the typical usage pattern.
// It's based on the approach described in https://www.nico.fyi/blog/react-server-actions-returns-jsx
// where server actions are used to generate and return HTML components directly.
// While this works, be aware that it's not the standard pattern recommended in the React/Next.js documentation.

export const LoadMoreBlogCategoryArticlesAction = async (
    blogCategoryUuid: string,
    currentPage: number,
    currentLoadMore: number,
) => {
    const blogCategoryArticlesPromise = getBlogCategoryArticlesQuery(
        blogCategoryUuid,
        getEndCursor(currentPage, currentLoadMore, DEFAULT_BLOG_PAGE_SIZE),
        DEFAULT_BLOG_PAGE_SIZE,
    );

    return <BlogArticlesServer blogArticlesPromise={blogCategoryArticlesPromise} />;
};
