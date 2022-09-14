import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { GetServerSidePropsContext } from 'next';
import getConfig from 'next/config';

type Url = string | { url: string; param: string | undefined | null };

const getInternationalizedStaticUrl = (url: Url, domainUrl: string, publicRuntimeConfig: any) => {
    const urlsOnDomain = publicRuntimeConfig.availableStaticUrls[domainUrl];

    if (typeof url === 'string') {
        const result = urlsOnDomain[url];
        return typeof result !== 'undefined' ? result : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate?.split(':')[0];

    return (staticPart ?? '') + (url.param ?? '');
};

export const getInternationalizedStaticUrls = (urls: Url[], domainUrl: string): string[] => {
    const { publicRuntimeConfig } = getConfig();

    return urls.map((url) => getInternationalizedStaticUrl(url, domainUrl, publicRuntimeConfig));
};

export const getServerSideInternationalizedStaticUrl = (
    context: GetServerSidePropsContext,
    domainUrl: string,
): string => {
    const { publicRuntimeConfig } = getConfig();
    const trimmedUrl = getUrlWithoutGetParameters(context.resolvedUrl);
    const result = getInternationalizedStaticUrl(trimmedUrl, domainUrl, publicRuntimeConfig);

    return result !== '' ? result : trimmedUrl;
};
