'use client';

import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { BlogCategoryHeader } from './BlogCategoryHeader';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { useRef } from 'react';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

type BlogCategoryContentProps = {
    blogCategory: TypeBlogCategoryDetailFragment;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    const title = useSeoTitleWithPagination(
        blogCategory.articlesTotalCount,
        blogCategory.name,
        undefined,
        DEFAULT_BLOG_PAGE_SIZE,
    );

    return (
        <div ref={paginationScrollTargetRef}>
            <BlogCategoryHeader description={blogCategory.description} image={blogCategory.mainImage} title={title} />
            <BlogLayout activeCategoryUuid={blogCategory.uuid}>
                <BlogCategoryArticlesWrapper
                    blogCategoryTotalCount={blogCategory.articlesTotalCount}
                    paginationScrollTargetRef={paginationScrollTargetRef}
                    uuid={blogCategory.uuid}
                />
            </BlogLayout>
        </div>
    );
};
