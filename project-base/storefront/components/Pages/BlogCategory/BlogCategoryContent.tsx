import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { Webline } from 'components/Layout/Webline/Webline';
import { BlogCategoryDetailFragment } from 'graphql/requests/blogCategories/fragments/BlogCategoryDetailFragment.generated';
import { useRef } from 'react';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

type BlogCategoryContentProps = {
    blogCategory: BlogCategoryDetailFragment;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    const title = useSeoTitleWithPagination(blogCategory.articlesTotalCount, blogCategory.name);

    return (
        <Webline>
            <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                <h1 className="mb-3">{title}</h1>
                <div className="mb-16 flex flex-col vl:flex-row">
                    <div className="order-2 mb-16 flex w-full flex-col vl:order-1 vl:flex-1">
                        <BlogCategoryArticlesWrapper
                            paginationScrollTargetRef={paginationScrollTargetRef}
                            uuid={blogCategory.uuid}
                        />
                    </div>
                    <div className="order-1 mb-7 flex w-full flex-col vl:order-2 vl:w-[435px] vl:pl-12">
                        <BlogSignpost
                            activeItem={blogCategory.uuid}
                            blogCategoryItems={blogCategory.blogCategoriesTree}
                        />
                    </div>
                </div>
            </div>
        </Webline>
    );
};
