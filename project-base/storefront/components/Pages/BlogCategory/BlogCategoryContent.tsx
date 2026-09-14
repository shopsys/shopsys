import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TypeBlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { useRef } from 'react';
import { useHeadingWithPagination } from 'utils/seo/useHeadingWithPagination';
import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { BlogCategoryHeader } from './BlogCategoryHeader';

type BlogCategoryContentProps = {
    blogCategory: TypeBlogCategoryDetailFragment;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    const heading = useHeadingWithPagination(
        blogCategory.seo.h1 || blogCategory.name,
        blogCategory.articlesTotalCount,
        DEFAULT_BLOG_PAGE_SIZE,
    );

    return (
        <VerticalStack gap="lg">
            <BlogCategoryHeader
                description={blogCategory.description}
                heading={heading}
                image={blogCategory.mainImage}
            />

            <BlogLayout activeCategoryUuid={blogCategory.uuid}>
                <BlogCategoryArticlesWrapper
                    blogCategoryTotalCount={blogCategory.articlesTotalCount}
                    paginationScrollTargetRef={paginationScrollTargetRef}
                    uuid={blogCategory.uuid}
                />
            </BlogLayout>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
