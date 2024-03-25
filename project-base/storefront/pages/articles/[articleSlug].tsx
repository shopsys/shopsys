import { CommonLayout } from 'components/Layout/CommonLayout';
import { ArticleDetailContent } from 'components/Pages/Article/ArticleDetailContent';
import {
    useArticleDetailQuery,
    ArticleDetailQuery,
    ArticleDetailQueryVariables,
    ArticleDetailQueryDocument,
} from 'graphql/requests/articles/queries/ArticleDetailQuery.generated';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const ArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: articleDetailData, fetching }] = useArticleDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const article = articleDetailData?.article?.__typename === 'ArticleSite' ? articleDetailData.article : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(article);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout breadcrumbs={article?.breadcrumb} canonicalQueryParams={[]} isFetchingData={fetching}>
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

            if (isRedirectedFromSsr(context.req.headers)) {
                const articleResponse: OperationResult<ArticleDetailQuery, ArticleDetailQueryVariables> = await client!
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

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    articleResponse.error?.graphQLErrors,
                    articleResponse.data?.article,
                    context.res,
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
