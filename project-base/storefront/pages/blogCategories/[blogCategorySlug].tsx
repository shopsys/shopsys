import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogCategoryContent } from 'components/Pages/BlogCategory/BlogCategoryContent';
import { DEFAULT_BLOG_PAGE_SIZE, DEFAULT_PAGE_SIZE } from 'config/constants';
import { BlogCategoriesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoriesQuery.generated';
import { BlogCategoryArticlesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import {
    BlogCategoryQueryDocument,
    useBlogCategoryQuery,
} from 'graphql/requests/blogCategories/queries/BlogCategoryQuery.generated';
import { useGtmFriendlyPageReadyEvent } from 'gtm/factories/useGtmFriendlyPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const Error404Content = dynamic(
    () => import('components/Pages/ErrorPage/Error404Content').then((m) => m.Error404Content),
    { ssr: false },
);

const BlogCategoryPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: blogCategoryData, fetching: isBlogCategoryFetching }] = useBlogCategoryQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const seoTitle = useSeoTitleWithPagination(
        blogCategoryData?.blogCategory?.articlesTotalCount,
        blogCategoryData?.blogCategory?.name,
        blogCategoryData?.blogCategory?.seoTitle,
        DEFAULT_BLOG_PAGE_SIZE,
    );

    const pageReadyEvent = useGtmFriendlyPageReadyEvent(blogCategoryData?.blogCategory);
    useGtmPageReadyEvent(pageReadyEvent, isBlogCategoryFetching);

    if (!blogCategoryData && !isBlogCategoryFetching) {
        return <Error404Content />;
    }

    return (
        <CommonLayout
            breadcrumbs={blogCategoryData?.blogCategory?.breadcrumb}
            breadcrumbsType="blogCategory"
            description={blogCategoryData?.blogCategory?.seoMetaDescription}
            hreflangLinks={blogCategoryData?.blogCategory?.hreflangLinks}
            isFetchingData={isBlogCategoryFetching}
            title={seoTitle}
        >
            {!!blogCategoryData?.blogCategory && <BlogCategoryContent blogCategory={blogCategoryData.blogCategory} />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                domainConfig,
                redisClient,
                context,
            });
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

            const blogCategoryPromise = client
                .query(BlogCategoryQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            const [blogCategoryResponse, layoutResult] = await Promise.all([
                blogCategoryPromise,
                prefetchLayoutQueries({
                    client,
                    context,
                    domainConfig,
                    prefetchedQueries: [{ query: BlogCategoriesDocument }],
                }),
            ]);

            const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                blogCategoryResponse.error,
                blogCategoryResponse.data?.blogCategory,
                context,
                domainConfig.url,
            );

            if (serverSideErrorResponse) {
                return serverSideErrorResponse;
            }

            const blogCategoryUuid = blogCategoryResponse.data?.blogCategory?.uuid;
            if (blogCategoryUuid) {
                await client
                    .query(BlogCategoryArticlesDocument, {
                        uuid: blogCategoryUuid,
                        endCursor: getEndCursor(page),
                        pageSize: DEFAULT_PAGE_SIZE,
                    })
                    .toPromise();
            }

            return buildServerSideProps({
                layoutResult,
                client,
                redisClient,
                ssrExchange,
                context,
                domainConfig,
                pageQueryResults: [blogCategoryResponse],
            });
        },
);

export default BlogCategoryPage;
