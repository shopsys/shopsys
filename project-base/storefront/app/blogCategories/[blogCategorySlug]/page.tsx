import { BlogArticlesServer } from 'app/_components/Page/BlogCategory/BlogArticlesServer';
import { BlogCategoryPagination } from 'app/_components/Page/BlogCategory/BlogCategoryPagination';
import { getBlogCategoryArticlesQuery } from 'app/_queries/getBlogCategoryArticlesQuery';
import { getBlogCategoryDetailQuery } from 'app/_queries/getBlogCategoryDetailQuery';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { TypeBlogArticleConnectionFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.ssr';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

type Params = { blogCategorySlug: string };
type SearchParams = { [key: string]: string | string[] | undefined };

const BlogCategoryPage = async ({
    params: { blogCategorySlug },
    searchParams,
}: {
    params: Params;
    searchParams: SearchParams;
}) => {
    const currentPage =
        typeof searchParams[PAGE_QUERY_PARAMETER_NAME] === 'string'
            ? Number(searchParams[PAGE_QUERY_PARAMETER_NAME])
            : 1;
    const currentLoadMore =
        typeof searchParams[LOAD_MORE_QUERY_PARAMETER_NAME] === 'string'
            ? Number(searchParams[LOAD_MORE_QUERY_PARAMETER_NAME])
            : 0;
    const blogCategoryData = await getBlogCategoryDetailQuery(blogCategorySlug);

    if (!blogCategoryData) {
        return notFound();
    }

    const endCursors: string[] = [];
    let lastPromise: Promise<TypeBlogArticleConnectionFragment | undefined> | null = null;

    for (let i = 0; i < currentLoadMore + 1; i++) {
        endCursors.push(getEndCursor(currentPage, i, DEFAULT_BLOG_PAGE_SIZE));
        lastPromise = getBlogCategoryArticlesQuery(
            blogCategoryData.uuid,
            getEndCursor(currentPage, i, DEFAULT_BLOG_PAGE_SIZE),
            DEFAULT_BLOG_PAGE_SIZE,
        );
    }

    // const seoTitle = useSeoTitleWithPagination(
    //     blogCategoryData?.articlesTotalCount,
    //     blogCategoryData?.name,
    //     blogCategoryData?.seoTitle,
    //     DEFAULT_BLOG_PAGE_SIZE,
    // );

    // const pageViewEvent = useGtmFriendlyPageViewEvent(blogCategoryData?.blogCategory);
    // useGtmPageViewEvent(pageViewEvent, isBlogCategoryFetching);

    return (
        <div className="vl:order-1 vl:flex-1 order-2 flex w-full flex-col gap-y-6 md:gap-y-10">
            <ul className="flex w-full flex-col gap-y-5">
                {endCursors.map((endCursor) => (
                    <Suspense
                        key={`batch:${endCursor}`}
                        fallback={
                            <div className="flex flex-col gap-y-5">
                                {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE).map((_, skeletonIndex) => (
                                    <SkeletonModuleArticleBlog key={`batch:${endCursor}-skeleton:${skeletonIndex}`} />
                                ))}
                            </div>
                        }
                    >
                        <BlogArticlesServer blogCategoryUuid={blogCategoryData.uuid} endCursor={endCursor} />
                    </Suspense>
                ))}
            </ul>
            <BlogCategoryPagination
                blogCategoryTotalCount={blogCategoryData.articlesTotalCount}
                blogCategoryUuid={blogCategoryData.uuid}
                hasNextPage={(await lastPromise)?.pageInfo.hasNextPage}
            />
        </div>
    );
};

export default BlogCategoryPage;
