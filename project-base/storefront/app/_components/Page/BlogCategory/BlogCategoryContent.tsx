'use client';

import { BlogCategoryProvider } from 'components/providers/BlogCategoryProvider';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { useRef } from 'react';

type BlogCategoryContentProps = {
    blogCategory: TypeBlogCategoryDetailFragment;
    header?: React.ReactNode;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ children, header, blogCategory }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    // const title = useSeoTitleWithPagination(
    //     blogCategory.articlesTotalCount,
    //     blogCategory.name,
    //     undefined,
    //     DEFAULT_BLOG_PAGE_SIZE,
    // );

    return (
        <div ref={paginationScrollTargetRef}>
            {header}
            <BlogCategoryProvider
                blogCategoryUuid={blogCategory.uuid}
                paginationScrollTargetRef={paginationScrollTargetRef}
            >
                {children}
            </BlogCategoryProvider>
        </div>
    );
};
