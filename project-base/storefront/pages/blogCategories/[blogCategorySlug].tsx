import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogCategoryContent } from 'components/Pages/BlogCategory/BlogCategoryContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { BlogCategoryArticlesDocument } from 'graphql/requests/blogCategories/queries/BlogCategoryArticlesQuery.generated';
import {
    useBlogCategoryQuery,
    BlogCategoryQuery,
    BlogCategoryQueryVariables,
    BlogCategoryQueryDocument,
} from 'graphql/requests/blogCategories/queries/BlogCategoryQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'utils/isRedirectedFromSsr';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const BlogCategoryPage: NextPage<ServerSidePropsType> = () => {
    const router = useRouter();
    const [{ data: blogCategoryData, fetching }] = useBlogCategoryQuery({
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
            hreflangLinks={blogCategoryData?.blogCategory?.hreflangLinks}
            isFetchingData={fetching}
            title={seoTitle}
        >
            {!!blogCategoryData?.blogCategory && <BlogCategoryContent blogCategory={blogCategoryData.blogCategory} />}
            <LastVisitedProducts />
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
                const blogCategoryResponse: OperationResult<BlogCategoryQuery, BlogCategoryQueryVariables> =
                    await client!
                        .query(BlogCategoryQueryDocument, {
                            urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                        })
                        .toPromise();

                await client!
                    .query(BlogCategoryArticlesDocument, {
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
