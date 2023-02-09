import { BlogCategoryArticlesWrapper } from './BlogCategoryArticlesWrapper';
import { HeadingPaginated } from 'components/Basic/Heading/HeadingPaginated';
import { BlogSignpost } from 'components/Blocks/BlogSignpost/BlogSignpost';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBlogCategoryArticlesApi } from 'graphql/generated';
import { FC } from 'react';
import { BlogCategoryDetailType } from 'types/blogCategory';

type BlogCategoryContentProps = {
    blogCategory: BlogCategoryDetailType;
};

export const BlogCategoryContent: FC<BlogCategoryContentProps> = ({ blogCategory }) => {
    const [{ endCursor }] = usePaginationContext();
    const [{ data }] = useBlogCategoryArticlesApi({
        variables: { uuid: blogCategory.uuid, endCursor: endCursor ?? '', pageSize: DEFAULT_PAGE_SIZE },
    });

    return (
        <Webline>
            <div>
                <HeadingPaginated type="h1" totalCount={data?.blogCategory?.blogArticles.totalCount ?? 0}>
                    {blogCategory.name}
                </HeadingPaginated>
                <div className="mb-16 flex flex-col vl:flex-row">
                    <div className="order-2 mb-16 flex w-full flex-col vl:order-1 vl:flex-1">
                        <BlogCategoryArticlesWrapper blogCategoryArticles={data} />
                    </div>
                    <div className="order-1 mb-7 flex w-full flex-col vl:order-2 vl:w-[435px] vl:pl-12">
                        <BlogSignpost
                            blogCategoryItems={blogCategory.blogCategoriesTree}
                            activeItem={blogCategory.uuid}
                        />
                    </div>
                </div>
            </div>
        </Webline>
    );
};
