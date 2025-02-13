import { Url } from 'app/_utils/getInternationalizedStaticUrls';
import { useAppConfig } from 'components/providers/AppConfigProvider';
import { SameLengthOutput } from 'types/SameLengthOutput';

export const useInternationalizedStaticUrls = <InputUrls extends Url[]>(urls: [...InputUrls]) => {
    const staticRewritePaths = useAppConfig((settings) => settings.staticRewritePaths);

    return urls.map((url) => getInternationalizedStaticUrl(url, staticRewritePaths)) as SameLengthOutput<InputUrls>;
};

const getInternationalizedStaticUrl = (url: Url, staticRewritePaths: Record<string, string>) => {
    if (typeof url === 'string') {
        const internationalizedUrl = staticRewritePaths[url];
        return typeof internationalizedUrl !== 'undefined' ? internationalizedUrl : '';
    }

    const staticUrlTemplate = staticRewritePaths[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};
