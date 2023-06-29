import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { BlogCategoryContent } from 'components/Pages/BlogCategory/BlogCategoryContent';
import { BlogCategoryPageSkeleton } from 'components/Pages/BlogCategory/BlogCategoryPageSkeleton';
import {
    BlogCategoryArticlesDocumentApi,
    BlogCategoryQueryApi,
    BlogCategoryQueryDocumentApi,
    BlogCategoryQueryVariablesApi,
    useBlogCategoryQueryApi,
} from 'graphql/generated';
import { getDomainConfig } from 'helpers/domain/domain';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { createClient } from 'helpers/urql/createClient';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult, ssrExchange } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';

const BlogCategoryPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const [{ data: blogCategoryData, fetching }] = useQueryError(
        useBlogCategoryQueryApi({
            variables: { urlSlug: getSlugFromUrl(slug) },
        }),
    );

    const seoTitle = useSeoTitleWithPagination(
        blogCategoryData?.blogCategory?.articlesTotalCount,
        blogCategoryData?.blogCategory?.name,
        blogCategoryData?.blogCategory?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(blogCategoryData?.blogCategory);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout title={seoTitle} description={blogCategoryData?.blogCategory?.seoMetaDescription}>
            {!!blogCategoryData?.blogCategory?.breadcrumb && (
                <Webline>
                    <Breadcrumbs
                        type="blogCategory"
                        key="breadcrumb"
                        breadcrumb={blogCategoryData.blogCategory.breadcrumb}
                    />
                </Webline>
            )}
            {!!blogCategoryData?.blogCategory && !fetching ? (
                <BlogCategoryContent blogCategory={blogCategoryData.blogCategory} />
            ) : (
                <BlogCategoryPageSkeleton />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const domainConfig = getDomainConfig(context.req.headers.host!);
    const ssrCache = ssrExchange({ isClient: false });
    const client = createClient(context, domainConfig.publicGraphqlEndpoint, ssrCache, redisClient);
    const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);

    if (isRedirectedFromSsr(context.req.headers)) {
        const blogCategoryResponse: OperationResult<BlogCategoryQueryApi, BlogCategoryQueryVariablesApi> = await client!
            .query(BlogCategoryQueryDocumentApi, {
                urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
            })
            .toPromise();

        await client!
            .query(BlogCategoryArticlesDocumentApi, {
                uuid: blogCategoryResponse.data?.blogCategory?.uuid,
                endCursor: getEndCursor(page),
                pageSize: DEFAULT_PAGE_SIZE,
            })
            .toPromise();

        if (
            (!blogCategoryResponse.data || !blogCategoryResponse.data.blogCategory) &&
            !(context.res.statusCode === 503)
        ) {
            return {
                notFound: true,
            };
        }
    }

    const initServerSideData = await initServerSideProps({
        context,
        client,
        ssrCache,
        redisClient,
    });

    return initServerSideData;
});

export default BlogCategoryPage;
