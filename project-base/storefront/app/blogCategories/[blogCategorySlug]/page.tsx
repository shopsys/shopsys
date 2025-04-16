import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { BlogLayout } from 'app/_components/Layout/BlogLayout';
import { Container } from 'app/_components/Layout/Container/Container';
import { BlogArticlesList } from 'app/_components/Page/BlogCategory/BlogArticlesList';
import { BlogCategoryContent } from 'app/_components/Page/BlogCategory/BlogCategoryContent';
import { BlogCategoryHeader } from 'app/_components/Page/BlogCategory/BlogCategoryHeader';
import { getBlogCategoryArticlesQuery } from 'app/_queries/getBlogCategoryArticlesQuery';
import { getBlogCategoryDetailQuery } from 'app/_queries/getBlogCategoryDetailQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { SkeletonModuleArticleBlog } from 'components/Blocks/Skeleton/SkeletonModuleArticleBlog';
import { Webline } from 'components/Layout/Webline/Webline';
import { DEFAULT_BLOG_PAGE_SIZE } from 'config/constants';
import { notFound } from 'next/navigation';
import { Suspense } from 'react';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';
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
    const t = await getTranslation();
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

    // prefetch blog category articles
    getBlogCategoryArticlesQuery(
        blogCategoryData.uuid,
        getEndCursor(currentPage, 0, DEFAULT_BLOG_PAGE_SIZE),
        calculatePageSize(currentLoadMore, DEFAULT_BLOG_PAGE_SIZE),
    );

    const totalCount = blogCategoryData.articlesTotalCount;

    let title = blogCategoryData.name;

    if (currentLoadMore > 0) {
        const totalPages = Math.ceil(totalCount / DEFAULT_BLOG_PAGE_SIZE);
        title = `${title} ${t('page {{ currentPage }} to {{ currentPageWithLoadMore }} from {{ totalPages }}', {
            currentPage,
            totalPages,
            currentPageWithLoadMore: Math.min(currentPage + currentLoadMore, totalPages),
        })}`;
    } else if (currentPage > 1) {
        title = `${title} ${t('page {{ currentPage }} from {{ totalPages }}', {
            currentPage,
            totalPages: Math.ceil(totalCount / DEFAULT_BLOG_PAGE_SIZE),
        })}`;
    }

    // const seoTitle = useSeoTitleWithPagination(
    //     blogCategoryData?.articlesTotalCount,
    //     blogCategoryData?.name,
    //     blogCategoryData?.seoTitle,
    //     DEFAULT_BLOG_PAGE_SIZE,
    // );

    // const pageViewEvent = useGtmFriendlyPageViewEvent(blogCategoryData?.blogCategory);
    // useGtmPageViewEvent(pageViewEvent, isBlogCategoryFetching);

    const suspenseKey = `page:${currentPage ?? 1}-lm:${currentLoadMore ?? 0}`;
    return (
        <Container>
            <BlogCategoryContent
                blogCategory={blogCategoryData}
                header={
                    <BlogCategoryHeader
                        description={blogCategoryData.description}
                        image={blogCategoryData.mainImage}
                        title={title}
                    />
                }
            >
                <BlogLayout activeCategoryUuid={blogCategoryData.uuid}>
                    <div className="vl:order-1 vl:flex-1 order-2 flex w-full flex-col gap-y-6 md:gap-y-10">
                        {/* TODO: Loading skeletons only for newly fetched articles */}
                        <Suspense
                            key={suspenseKey}
                            fallback={
                                <div className="flex flex-col gap-y-5">
                                    {createEmptyArray(DEFAULT_BLOG_PAGE_SIZE * (currentLoadMore + 1)).map(
                                        (_, index) => (
                                            <SkeletonModuleArticleBlog key={suspenseKey + index} />
                                        ),
                                    )}
                                </div>
                            }
                        >
                            <BlogArticlesList
                                blogCategoryTotalCount={blogCategoryData.articlesTotalCount}
                                blogCategoryUuid={blogCategoryData.uuid}
                                currentLoadMore={currentLoadMore}
                                currentPage={currentPage}
                            />
                        </Suspense>
                    </div>
                </BlogLayout>
            </BlogCategoryContent>
            <Webline>
                <LastVisitedProducts />
            </Webline>
        </Container>
    );
};

export default BlogCategoryPage;
