import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { headers } from 'next/headers';
import { getDomainConfig } from 'utils/domain/domainConfig';

export type Url = StaticRewritePathKeyType | { url: StaticRewritePathKeyType; param: string | undefined | null };

export const getInternationalizedStaticUrl = (url: Url) => {
    const domainConfig = getDomainConfig(headers().get('host')!);

    const urlsOnDomain = STATIC_REWRITE_PATHS[domainConfig.url];

    if (typeof url === 'string') {
        const internationalizedUrl = urlsOnDomain[url];
        return typeof internationalizedUrl !== 'undefined' ? internationalizedUrl : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};
