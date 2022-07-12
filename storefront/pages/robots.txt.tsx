import { GetServerSidePropsContext } from 'next';
import { FC } from 'react';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

const getRobotsTxtContent = (domain: string, domainId: number): string => {
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
            { url: '/order-detail/:urlHash', param: '*' },
        ],
        domain,
    );
    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], domain);

    return `User-Agent: *
${staticUrlsToNoIndex.map((page) => `\nDisallow: ${page}`).join('')}
Disallow: ${customerUrl}/*
Disallow: /admin
Disallow: *?filter=

Sitemap: ${domain}content/sitemaps/domain_${domainId}_sitemap.xml
Sitemap: ${domain}content/sitemaps/domain_${domainId}_sitemap_image.xml
`;
};

export const getServerSideProps = async (
    context: GetServerSidePropsContext,
): Promise<{ props: Record<string, never> }> => {
    const domain = context.req.headers.host;
    const domainConfig = getDomainConfig(domain);

    const res = context.res;

    res.setHeader('Content-Type', 'text/plain');
    res.write(getRobotsTxtContent(domainConfig.url, domainConfig.domainId));
    res.end();

    return { props: {} };
};

// mandatory for Next although it's not used
const Robots: FC = (): null => {
    return null;
};

export default Robots;
