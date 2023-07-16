import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import { BlogArticlePageSkeleton } from 'components/Pages/BlogArticle/BlogArticlePageSkeleton';
import {
    BlogArticleDetailQueryApi,
    BlogArticleDetailQueryDocumentApi,
    BlogArticleDetailQueryVariablesApi,
    ProductsByCatnumsDocumentApi,
    useBlogArticleDetailQueryApi,
} from 'graphql/generated';

import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { createClient } from 'helpers/urql/createClient';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { NextPage } from 'next';
import getT from 'next-translate/getT';
import { useRouter } from 'next/router';
import { OperationResult, ssrExchange } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';
import { parseCatnums } from 'utils/grapesJsParser';

const BlogArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const [{ data: blogArticleData, fetching }] = useBlogArticleDetailQueryApi({
        variables: { urlSlug: getSlugFromUrl(slug) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(blogArticleData?.blogArticle);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            title={blogArticleData?.blogArticle?.seoTitle}
            description={blogArticleData?.blogArticle?.seoMetaDescription}
        >
            {!!blogArticleData?.blogArticle?.breadcrumb && (
                <Webline>
                    <Breadcrumbs
                        key="breadcrumb"
                        type="blogCategory"
                        breadcrumb={blogArticleData.blogArticle.breadcrumb}
                    />
                </Webline>
            )}
            {!!blogArticleData?.blogArticle && !fetching ? (
                <BlogArticleDetailContent blogArticle={blogArticleData.blogArticle} />
            ) : (
                <BlogArticlePageSkeleton />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient, domainConfig) => async (context) => {
    const ssrCache = ssrExchange({ isClient: false });
    const t = await getT(domainConfig.defaultLocale, 'common');
    const client = createClient(t, ssrCache, domainConfig.publicGraphqlEndpoint, redisClient, context);

    if (isRedirectedFromSsr(context.req.headers)) {
        const blogArticleResponse: OperationResult<BlogArticleDetailQueryApi, BlogArticleDetailQueryVariablesApi> =
            await client!
                .query(BlogArticleDetailQueryDocumentApi, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                })
                .toPromise();

        const parsedCatnums = parseCatnums(blogArticleResponse.data?.blogArticle?.text ?? '');

        await client!
            .query(ProductsByCatnumsDocumentApi, {
                catnums: parsedCatnums,
            })
            .toPromise();

        if ((!blogArticleResponse.data || !blogArticleResponse.data.blogArticle) && !(context.res.statusCode === 503)) {
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

export default BlogArticleDetailPage;
