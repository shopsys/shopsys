import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';

export type Url = StaticRewritePathKeyType | { url: StaticRewritePathKeyType; param: string | undefined | null };

export const getInternationalizedStaticUrl = (url: Url, domainUrl: string) => {
    const urlsOnDomain = STATIC_REWRITE_PATHS[domainUrl];

    if (typeof url === 'string') {
        const internationalizedUrl = urlsOnDomain[url];
        return typeof internationalizedUrl !== 'undefined' ? internationalizedUrl : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};
