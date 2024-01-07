import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogCategoryContent } from 'components/Pages/BlogCategory/BlogCategoryContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    BlogCategoryArticlesDocumentApi,
    BlogCategoryQueryApi,
    BlogCategoryQueryDocumentApi,
    BlogCategoryQueryVariablesApi,
    useBlogCategoryQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { getNumberFromUrlQuery, getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const BlogCategoryPage: NextPage = () => {
    const router = useRouter();
    const [{ data: blogCategoryData, fetching }] = useBlogCategoryQueryApi({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const seoTitle = useSeoTitleWithPagination(
        blogCategoryData?.blogCategory?.articlesTotalCount,
        blogCategoryData?.blogCategory?.name,
        blogCategoryData?.blogCategory?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(blogCategoryData?.blogCategory);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            breadcrumbs={blogCategoryData?.blogCategory?.breadcrumb}
            breadcrumbsType="blogCategory"
            description={blogCategoryData?.blogCategory?.seoMetaDescription}
            isFetchingData={fetching}
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
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);

            if (isRedirectedFromSsr(context.req.headers)) {
                const blogCategoryResponse: OperationResult<BlogCategoryQueryApi, BlogCategoryQueryVariablesApi> =
                    await client!
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

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    blogCategoryResponse.error?.graphQLErrors,
                    blogCategoryResponse.data?.blogCategory,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                domainConfig,
                ssrExchange,
            });

            return initServerSideData;
        },
);

export default BlogCategoryPage;
