import {
    getQueryWithoutSlugTypeParameterFromQueryString,
    getUrlWithoutGetParameters,
} from 'helpers/parsing/urlParsing';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import getConfig from 'next/config';

type Url = string | { url: string; param: string | undefined | null };

const getInternationalizedStaticUrl = (url: Url, domainUrl: string) => {
    const { publicRuntimeConfig } = getConfig();
    const urlsOnDomain = publicRuntimeConfig.staticRewritePaths[domainUrl];

    if (typeof url === 'string') {
        const result = urlsOnDomain[url];
        return typeof result !== 'undefined' ? result : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate?.split(':')[0];

    return (staticPart ?? '') + (url.param ?? '');
};

export const getInternationalizedStaticUrls = (urls: Url[], domainUrl: string): string[] => {
    return urls.map((url) => getInternationalizedStaticUrl(url, domainUrl));
};

export const getServerSideInternationalizedStaticUrl = (
    context: GetServerSidePropsContext | NextPageContext,
    domainUrl: string,
) => {
    if (!('resolvedUrl' in context)) {
        return { trimmedUrlWithoutQueryParams: '/', queryParams: null };
    }

    const trimmedUrlWithoutQueryParams = getUrlWithoutGetParameters(context.resolvedUrl);
    const result = getInternationalizedStaticUrl(trimmedUrlWithoutQueryParams, domainUrl);
    const queryParams = getQueryWithoutSlugTypeParameterFromQueryString(context.resolvedUrl.split('?')[1]);

    return {
        trimmedUrlWithoutQueryParams: result || trimmedUrlWithoutQueryParams,
        queryParams: queryParams ? `?${queryParams}` : null,
    };
};
