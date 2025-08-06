import {
    RobotsTxtQueryDocument,
    TypeRobotsTxtQuery,
    TypeRobotsTxtQueryVariables,
} from 'graphql/requests/robotsTxt/RobotsTxtQuery.generated';
import { createClient } from 'urql/createClient';
import { getPublicConfigProperty } from 'utils/config/getNextConfig';
import { DomainConfigType, getDomainConfig } from 'utils/domain/domainConfig';
import { DEFAULT_LOCALE, getHostFromDomain } from 'utils/domain/domainUtils';
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
const domains = getPublicConfigProperty('domains', []) as DomainConfigType[];

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, t, ssrExchange }) => async (context) => {
    const domainConfig = getDomainConfig(context);
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
    res.write(getRobotsTxtContent(domainConfig.url, robotsTxtResponse.data?.settings?.seo.robotsTxtContent));
    res.end();

    return { props: {} };
});

const getRobotsTxtContent = (domain: string, robotsTxtContentFromAdmin: string | null | undefined): string => {
    const host = getHostFromDomain(domain);
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

    const staticUrlsToNoIndex = domains.flatMap((domainConfig) => {
        const internationalizedUrls = getInternationalizedStaticUrls(urlsToInternationalize, domainConfig.url);
        const localePrefixUrl = domainConfig.defaultLocale === DEFAULT_LOCALE ? '' : `/${domainConfig.defaultLocale}`;
        return internationalizedUrls.map((url) => `${localePrefixUrl}${url}`);
    });

    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], domain);

    return `User-Agent: *
${staticUrlsToNoIndex.map((page) => `\nDisallow: ${page}`).join('')}
Disallow: ${customerUrl}/*
Disallow: *?${FILTER_QUERY_PARAMETER_NAME}=
Disallow: *?${LOAD_MORE_QUERY_PARAMETER_NAME}=
Disallow: *?${SORT_QUERY_PARAMETER_NAME}=
Disallow: /*?width=
${robotsTxtContentFromAdmin || ''}

Sitemap: ${host}content/sitemaps/sitemap.xml
Sitemap: ${host}content/sitemaps/sitemap_image.xml`;
};

export default Robots;
