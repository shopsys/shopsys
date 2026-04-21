import { ArticleMetadata } from 'components/Basic/Head/ArticleMetadata';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { ArticleDetailContent } from 'components/Pages/Article/ArticleDetailContent';
import {
    ArticleDetailQueryDocument,
    useArticleDetailQuery,
} from 'graphql/requests/articles/queries/ArticleDetailQuery.generated';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OgTypeEnum } from 'types/seo';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries } from 'utils/serverSide/initServerSideProps';

const Error404Content = dynamic(
    () => import('components/Pages/ErrorPage/Error404Content').then((m) => m.Error404Content),
    { ssr: false },
);

const ArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: articleDetailData, fetching: isArticleDetailFetching }] = useArticleDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const article = articleDetailData?.article?.__typename === 'ArticleSite' ? articleDetailData.article : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(article);
    useGtmPageViewEvent(pageViewEvent, isArticleDetailFetching);

    if (!articleDetailData && !isArticleDetailFetching) {
        return <Error404Content />;
    }

    return (
        <CommonLayout
            breadcrumbs={article?.breadcrumb}
            canonicalQueryParams={[]}
            description={article?.seoMetaDescription}
            isFetchingData={isArticleDetailFetching}
            ogType={OgTypeEnum.Article}
            title={article?.seoTitle || article?.articleName}
        >
            {!!article && (
                <>
                    <ArticleMetadata
                        datePublished={article.createdAt}
                        description={article.seoMetaDescription}
                        headline={article.seoTitle || article.articleName}
                    />
                    <ArticleDetailContent article={article} />
                </>
            )}
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

            const articlePromise = client
                .query(ArticleDetailQueryDocument, {
                    urlSlug: getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers),
                })
                .toPromise();

            const [articleResponse, layoutResult] = await Promise.all([
                articlePromise,
                prefetchLayoutQueries({ client, context, domainConfig }),
            ]);

            const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                articleResponse.error,
                articleResponse.data?.article,
                context,
                domainConfig.url,
            );

            if (serverSideErrorResponse) {
                return serverSideErrorResponse;
            }

            const article =
                articleResponse.data?.article?.__typename === 'ArticleSite' ? articleResponse.data.article : null;

            const parsedCatnums = parseCatnums(article?.text ?? '');
            if (parsedCatnums.length > 0) {
                await client.query(ProductsByCatnumsDocument, { catnums: parsedCatnums }).toPromise();
            }

            return buildServerSideProps({
                layoutResult,
                client,
                ssrExchange,
                context,
                domainConfig,
                pageQueryResults: [articleResponse],
            });
        },
);

export default ArticleDetailPage;
