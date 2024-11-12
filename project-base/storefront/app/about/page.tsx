import { getUrqlExchanges } from 'app/_urql/exchanges';
import {
    BlogArticleDetailQueryDocument,
    TypeBlogArticleDetailQuery,
    TypeBlogArticleDetailQueryVariables,
} from 'graphql/requests/articlesInterface/blogArticles/queries/BlogArticleDetailQuery.generated';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
    TypeCurrentCustomerUserQueryVariables,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import getConfig from 'next/config';
import { headers } from 'next/headers';
// eslint-disable-next-line no-restricted-imports
import { createClient, OperationResult, ssrExchange } from 'urql';
import { fetcher } from 'urql/fetcher';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { getServerT } from 'utils/getServerTranslation';

async function getRedis() {
    const createRedisClient = (await import('redis')).createClient;

    const redisClient = createRedisClient({
        url: `redis://${process.env.REDIS_HOST}`,
        socket: {
            connectTimeout: 5000,
        },
    });

    return redisClient;
}

async function getClient() {
    const t = await getServerT();
    const { serverRuntimeConfig } = getConfig();
    const internalGraphqlEndpoint = serverRuntimeConfig?.internalGraphqlEndpoint ?? undefined;

    const domainConfig = getDomainConfig(headers().get('host')!);

    const publicGraphqlEndpoint = domainConfig.publicGraphqlEndpoint;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

    const redisClient = await getRedis();

    await redisClient.connect();

    const client = createClient({
        url: internalGraphqlEndpoint ?? publicGraphqlEndpoint,
        exchanges: await getUrqlExchanges(ssrExchange({ isClient: false }), t),
        fetchOptions: {
            headers: {
                OriginalHost: publicGraphqlEndpointObject.host,
                'X-Forwarded-Proto': publicGraphqlEndpointObject.protocol === 'https:' ? 'on' : 'off',
            },
            cache: 'no-store',
        },
        fetch: fetcher(redisClient),
    });

    redisClient.disconnect();

    return client;
}

async function getArticle() {
    const client = await getClient();

    const blogArticleResponse: OperationResult<TypeBlogArticleDetailQuery, TypeBlogArticleDetailQueryVariables> =
        await client.query(BlogArticleDetailQueryDocument, {
            urlSlug: 'blog-article-example-2-en',
        });

    return blogArticleResponse;
}

async function getCustomerUser() {
    const client = await getClient();

    const customerUserResponse: OperationResult<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables> =
        await client.query(CurrentCustomerUserQueryDocument, {});

    return customerUserResponse;
}

export default async function IndexPage() {
    const t = await getServerT();
    const { data } = await getArticle();
    const { data: customerUserData } = await getCustomerUser();

    return (
        <div>
            <h1>{data?.blogArticle?.name}</h1>
            <div>
                <p>This text is rendered on the server: {t('Delivery in {{count}} days', { count: 1 })}</p>
                {customerUserData?.currentCustomerUser && (
                    <p>
                        Logged in user:{' '}
                        {`${customerUserData.currentCustomerUser.firstName} ${customerUserData.currentCustomerUser.lastName}`}
                    </p>
                )}
            </div>
        </div>
    );
}
