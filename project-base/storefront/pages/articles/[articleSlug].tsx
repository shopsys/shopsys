import { CommonLayout } from 'components/Layout/CommonLayout';
import { ArticleDetailContent } from 'components/Pages/Article/ArticleDetailContent';
import {
    useArticleDetailQuery,
    TypeArticleDetailQuery,
    TypeArticleDetailQueryVariables,
    ArticleDetailQueryDocument,
} from 'graphql/requests/articles/queries/ArticleDetailQuery.generated';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OgTypeEnum } from 'types/seo';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const ArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: articleDetailData, fetching: isArticleDetailFetching }] = useArticleDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const article = articleDetailData?.article?.__typename === 'ArticleSite' ? articleDetailData.article : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(article);
    useGtmPageViewEvent(pageViewEvent, isArticleDetailFetching);

    return (
        <CommonLayout
            breadcrumbs={article?.breadcrumb}
            canonicalQueryParams={[]}
            description={article?.seoMetaDescription}
            isFetchingData={isArticleDetailFetching}
            ogType={OgTypeEnum.Article}
            title={article?.seoTitle || article?.articleName}
        >
            {!!article && <ArticleDetailContent article={article} />}
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

            const articleResponse: OperationResult<TypeArticleDetailQuery, TypeArticleDetailQueryVariables> =
                await client!
                    .query(ArticleDetailQueryDocument, {
                        urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                    })
                    .toPromise();

            const article =
                articleResponse.data?.article?.__typename === 'ArticleSite' ? articleResponse.data.article : null;

            const parsedCatnums = parseCatnums(article?.text ?? '');

            await client!
                .query(ProductsByCatnumsDocument, {
                    catnums: parsedCatnums,
                })
                .toPromise();

            if (getIsRedirectedFromSsr(context.req.headers)) {
                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    articleResponse.error,
                    articleResponse.data?.article,
                    context.res,
                    domainConfig.url,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
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
