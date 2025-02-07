import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'app/_config/staticRewritePaths';
import { getDomainConfigServer } from 'app/_utils/domain/domainConfigServer';
import { headers } from 'next/headers';
import { SameLengthOutput } from 'types/SameLengthOutput';

export type Url = StaticRewritePathKeyType | { url: StaticRewritePathKeyType; param: string | undefined | null };

export const getInternationalizedStaticUrlsServer = <InputUrls extends Url[]>(urls: [...InputUrls]) => {
    return urls.map((url) => getInternationalizedStaticUrlServer(url)) as SameLengthOutput<InputUrls>;
};

export const getInternationalizedStaticUrlServer = (url: Url) => {
    const domainConfig = getDomainConfigServer(headers().get('host')!);

    const urlsOnDomain = STATIC_REWRITE_PATHS[domainConfig.url];

    if (typeof url === 'string') {
        const internationalizedUrl = urlsOnDomain[url];
        return typeof internationalizedUrl !== 'undefined' ? internationalizedUrl : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};
