import {
    TypeRobotsTxtQuery,
    TypeRobotsTxtQueryVariables,
    RobotsTxtQueryDocument,
} from 'graphql/requests/robotsTxt/RobotsTxtQuery.generated';
import { createClient } from 'urql/createClient';
import { getDomainConfig } from 'utils/domain/domainConfig';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

// mandatory for Next although it's not used
const Robots: FC = (): null => {
    return null;
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, t, ssrExchange }) => async (context) => {
    const domain = context.req.headers.host!;
    const domainConfig = getDomainConfig(domain);
    const client = await createClient({
        publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
        ssrExchange,
        redisClient,
        context,
        t,
    });

    const robotsTxtResponse = await client
        .query<TypeRobotsTxtQuery, TypeRobotsTxtQueryVariables>(RobotsTxtQueryDocument, {})
        .toPromise();

    const res = context.res;

    res.setHeader('Content-Type', 'text/plain');
    res.write(
        getRobotsTxtContent(
            domainConfig.url,
            domainConfig.domainId,
            robotsTxtResponse.data?.settings?.seo.robotsTxtContent,
        ),
    );
    res.end();

    return { props: {} };
});

const getRobotsTxtContent = (
    domain: string,
    domainId: number,
    robotsTxtContentFromAdmin: string | null | undefined,
): string => {
    const staticUrlsToNoIndex = getInternationalizedStaticUrls(
        [
            '/cart',
            '/new-password',
            '/search',
            '/order-confirmation',
            '/order-payment-confirmation',
            '/personal-data-export',
            '/personal-data-overview',
            '/order/contact-information',
            '/order/transport-and-payment',
            '/grapesjs-template',
            { url: '/order-detail/:urlHash', param: '*' },
        ],
        domain,
    );
    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], domain);

    return `User-Agent: *
    ${staticUrlsToNoIndex.map((page) => `\nDisallow: ${page}`).join('')}
Disallow: ${customerUrl}/*
Disallow: *?${FILTER_QUERY_PARAMETER_NAME}=
Disallow: *?${LOAD_MORE_QUERY_PARAMETER_NAME}=
Disallow: *?${SORT_QUERY_PARAMETER_NAME}=
${robotsTxtContentFromAdmin || ''}

Sitemap: ${domain}content/sitemaps/domain_${domainId}_sitemap.xml
Sitemap: ${domain}content/sitemaps/domain_${domainId}_sitemap_image.xml`;
};

export default Robots;
