import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { useRef } from 'react';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

type BlogCategoryContentProps = {
    blogCategory: TypeBlogCategoryDetailFragment;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    const title = useSeoTitleWithPagination(blogCategory.articlesTotalCount, blogCategory.name);

    return (
        <BlogLayout activeCategoryUuid={blogCategory.uuid}>
            <div className="order-2 mb-16 flex w-full flex-col vl:order-1 vl:flex-1">
                <h1>{title}</h1>
                <BlogCategoryArticlesWrapper
                    paginationScrollTargetRef={paginationScrollTargetRef}
                    uuid={blogCategory.uuid}
                />
            </div>
        </BlogLayout>
    );
};
