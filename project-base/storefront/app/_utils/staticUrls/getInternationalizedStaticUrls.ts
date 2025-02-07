import { Url } from './getInternationalizedStaticUrlsServer';
import { SameLengthOutput } from 'types/SameLengthOutput';

export const getInternationalizedStaticUrls = <InputUrls extends Url[]>(
    urls: [...InputUrls],
    staticRewritePaths: Record<string, string>,
) => {
    return urls.map((url) => getInternationalizedStaticUrl(url, staticRewritePaths)) as SameLengthOutput<InputUrls>;
};

export const getInternationalizedStaticUrl = (url: Url, staticRewritePaths: Record<string, string>) => {
    if (typeof url === 'string') {
        const internationalizedUrl = staticRewritePaths[url];
        return typeof internationalizedUrl !== 'undefined' ? internationalizedUrl : '';
    }

    const staticUrlTemplate = staticRewritePaths[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};
