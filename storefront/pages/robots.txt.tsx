import { GetServerSidePropsContext } from 'next';
import { FC } from 'react';
import { getDomainConfig } from 'utils/Domain/Domain';

const getRobotsTxtContent = (domain: string, domainId: number): string => {
    return `User-Agent: *

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
