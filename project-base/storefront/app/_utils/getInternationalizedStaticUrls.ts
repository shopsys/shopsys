import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'app/_config/staticRewritePaths';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { SameLengthOutput } from 'types/SameLengthOutput';

export type Url = StaticRewritePathKeyType | { url: StaticRewritePathKeyType; param: string | undefined | null };

export const getInternationalizedStaticUrls = async <InputUrls extends Url[]>(urls: [...InputUrls]) => {
    return urls.map((url) => getInternationalizedStaticUrl(url)) as SameLengthOutput<InputUrls>;
};

export const getInternationalizedStaticUrl = async (url: Url) => {
    const domainConfig = await getDomainConfig();

    const urlsOnDomain = STATIC_REWRITE_PATHS[domainConfig.url];

    if (typeof url === 'string') {
        const internationalizedUrl = urlsOnDomain[url];
        return typeof internationalizedUrl !== 'undefined' ? internationalizedUrl : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};
