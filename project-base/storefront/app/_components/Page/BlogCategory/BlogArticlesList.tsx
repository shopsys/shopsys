import { BlogArticlesServer } from './BlogArticlesServer';
import { BlogCategoryPagination } from './BlogCategoryPagination';
import { ClientBlogArticles } from './ClientBlogArticles';
import { getBlogCategoryArticlesQuery } from 'app/_queries/getBlogCategoryArticlesQuery';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';

type BlogArticlesListProps = {
    blogCategoryUuid: string;
    currentPage: number;
    currentLoadMore: number;
    blogCategoryTotalCount: number;
};

export const BlogArticlesList: FC<BlogArticlesListProps> = async ({
    blogCategoryUuid,
    currentPage,
    currentLoadMore,
    blogCategoryTotalCount,
}) => {
    // Fetch initial data on the server
    const blogCategoryArticlesPromise = getBlogCategoryArticlesQuery(
        blogCategoryUuid,
        getEndCursor(currentPage, 0, DEFAULT_BLOG_PAGE_SIZE),
        calculatePageSize(currentLoadMore, DEFAULT_BLOG_PAGE_SIZE),
    );


    return (
        <>
            <ul className="flex w-full flex-col gap-y-5">
                {/* Server-rendered initial articles */}
                <BlogArticlesServer blogArticlesPromise={blogCategoryArticlesPromise} />

                {/* Client component for additional articles (load more) */}
                <ClientBlogArticles
                    blogCategoryUuid={blogCategoryUuid}
                    initialLoadMore={currentLoadMore}
                    initialPage={currentPage}
                />
            </ul>

            <BlogCategoryPagination
                blogCategoryTotalCount={blogCategoryTotalCount}
                blogCategoryUuid={blogCategoryUuid}
                hasNextPage={(await blogCategoryArticlesPromise)?.pageInfo.hasNextPage}
            />
        </>
    );
};
