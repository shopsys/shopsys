'use client';

import { BlogCategoryProvider } from 'components/providers/BlogCategoryProvider';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { useRef } from 'react';

type BlogCategoryContentProps = {
    blogCategory: TypeBlogCategoryDetailFragment;
    header?: React.ReactNode;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ children, blogCategory }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    return (
        <div ref={paginationScrollTargetRef}>
            <BlogCategoryProvider
                blogCategoryUuid={blogCategory.uuid}
                paginationScrollTargetRef={paginationScrollTargetRef}
            >
                {children}
            </BlogCategoryProvider>
        </div>
    );
};
