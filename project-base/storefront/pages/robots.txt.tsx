import { getPublicConfigProperty } from 'envConfig';
import {
    RobotsTxtQueryDocument,
    TypeRobotsTxtQuery,
    TypeRobotsTxtQueryVariables,
} from 'graphql/requests/robotsTxt/RobotsTxtQuery.generated';
import { createClient } from 'urql/createClient';
import { DomainConfigType, getDomainConfig } from 'utils/domain/domainConfig';
import { DEFAULT_LOCALE, getHostFromDomain, getLocalePrefix } from 'utils/domain/domainUtils';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { Url } from 'utils/staticUrls/getInternationalizedStaticUrl';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

// mandatory for Next although it's not used
const Robots: FC = (): null => {
    return null;
};
const domains = getPublicConfigProperty('domains');

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, t, ssrExchange }) => async (context) => {
    const domainConfig = getDomainConfig(context);

    // Allow only root /robots.txt, return 404 for locale-prefixed URLs
    if (context.locale !== DEFAULT_LOCALE) {
        return {
            notFound: true,
        };
    }

    const client = await createClient({
        domainConfig,
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
    res.write(getRobotsTxtContent(domainConfig, robotsTxtResponse.data?.settings?.seo.robotsTxtContent));
    res.end();

    return { props: {} };
});

const getRobotsTxtContent = (
    currentDomainConfig: DomainConfigType,
    robotsTxtContentFromAdmin: string | null | undefined,
): string => {
    const host = getHostFromDomain(currentDomainConfig.url);
    const currentDomainHost = new URL(currentDomainConfig.url).host;
    const domainId = currentDomainConfig.domainId;

    // Find all domains with the same base URL (host)
    const domainsWithSameHost = domains.filter((domainConfig) => {
        const domainConfigHost = new URL(domainConfig.url).host;

        return domainConfigHost === currentDomainHost;
    });

    const urlsToInternationalize = [
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
        '/_feedback',
        '/styleguide',
        { url: '/order-detail/:urlHash', param: '*' },
    ] as Url[];

    // Aggregate static URLs from all domains with the same host
    const staticUrlsToNoIndex = domainsWithSameHost.flatMap((domainConfig) => {
        const internationalizedUrls = getInternationalizedStaticUrls(urlsToInternationalize, domainConfig.url);
        const localePrefixUrl = getLocalePrefix(domainConfig);

        return internationalizedUrls.map((url) => `${localePrefixUrl}${url}`);
    });

    // Aggregate customer URLs from all domains with the same host
    const customerUrlsToNoIndex = domainsWithSameHost.flatMap((domainConfig) => {
        const [customerUrl] = getInternationalizedStaticUrls(['/customer'], domainConfig.url);
        const localePrefixUrl = getLocalePrefix(domainConfig);

        return `${localePrefixUrl}${customerUrl}/*`;
    });

    return `User-Agent: *
${staticUrlsToNoIndex.map((page) => `\nDisallow: ${page}`).join('')}
${customerUrlsToNoIndex.map((customerUrl) => `\nDisallow: ${customerUrl}`).join('')}
Disallow: *?${FILTER_QUERY_PARAMETER_NAME}=
Disallow: *?${LOAD_MORE_QUERY_PARAMETER_NAME}=
Disallow: *?${SORT_QUERY_PARAMETER_NAME}=
Disallow: /*?width=
${robotsTxtContentFromAdmin || ''}

Sitemap: ${host}content/sitemaps/domain_${domainId}_sitemap.xml
Sitemap: ${host}content/sitemaps/domain_${domainId}_sitemap_image.xml`;
};

export default Robots;
