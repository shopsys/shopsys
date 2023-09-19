import { CommonLayout } from 'components/Layout/CommonLayout';
import { ArticleDetailContent } from 'components/Pages/Article/ArticleDetailContent';
import { ArticlePageSkeleton } from 'components/Pages/Article/ArticlePageSkeleton';
import {
    ArticleDetailQueryApi,
    ArticleDetailQueryDocumentApi,
    ArticleDetailQueryVariablesApi,
    ProductsByCatnumsDocumentApi,
    useArticleDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/isServer';
import { createClient } from 'urql/createClient';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';

const ArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: articleDetailData, fetching }] = useArticleDetailQueryApi({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const article = articleDetailData?.article?.__typename === 'ArticleSite' ? articleDetailData.article : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(article);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            title={article?.seoTitle}
            description={article?.seoMetaDescription}
            breadcrumbs={article?.breadcrumb}
            canonicalQueryParams={[]}
        >
            {!!article && !fetching ? <ArticleDetailContent article={article} /> : <ArticlePageSkeleton />}
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

            if (isRedirectedFromSsr(context.req.headers)) {
                const articleResponse: OperationResult<ArticleDetailQueryApi, ArticleDetailQueryVariablesApi> =
                    await client!
                        .query(ArticleDetailQueryDocumentApi, {
                            urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                        })
                        .toPromise();

                const article =
                    articleResponse.data?.article?.__typename === 'ArticleSite' ? articleResponse.data.article : null;

                const parsedCatnums = parseCatnums(article?.text ?? '');

                await client!
                    .query(ProductsByCatnumsDocumentApi, {
                        catnums: parsedCatnums,
                    })
                    .toPromise();

                if ((!articleResponse.data || !articleResponse.data.article) && !(context.res.statusCode === 503)) {
                    return {
                        notFound: true,
                    };
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                ssrExchange,
                domainConfig,
            });

            return initServerSideData;
        },
);

export default ArticleDetailPage;
