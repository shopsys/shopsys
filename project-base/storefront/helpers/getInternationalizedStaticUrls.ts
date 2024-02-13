import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import {
    getQueryWithoutSlugTypeParameterFromQueryString,
    getUrlWithoutGetParameters,
} from 'helpers/parsing/urlParsing';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { SameLengthOutput } from 'types/SameLengthOutput';

type Url = StaticRewritePathKeyType | { url: StaticRewritePathKeyType; param: string | undefined | null };

const getInternationalizedStaticUrl = (url: Url, domainUrl: string) => {
    const urlsOnDomain = STATIC_REWRITE_PATHS[domainUrl];

    if (typeof url === 'string') {
        const result = urlsOnDomain[url];
        return typeof result !== 'undefined' ? result : '';
    }

    const staticUrlTemplate = urlsOnDomain[url.url];
    const staticPart = staticUrlTemplate.split(':')[0];

    return staticPart + (url.param ?? '');
};

export const getInternationalizedStaticUrls = <InputUrls extends Url[]>(urls: [...InputUrls], domainUrl: string) => {
    return urls.map((url) => getInternationalizedStaticUrl(url, domainUrl)) as SameLengthOutput<InputUrls>;
};

export const getServerSideInternationalizedStaticUrl = (
    context: GetServerSidePropsContext | NextPageContext,
    domainUrl: string,
) => {
    if (!('resolvedUrl' in context)) {
        return { trimmedUrlWithoutQueryParams: '/', queryParams: null };
    }

    const trimmedUrlWithoutQueryParams = getUrlWithoutGetParameters(context.resolvedUrl);
    if (!(trimmedUrlWithoutQueryParams in STATIC_REWRITE_PATHS[domainUrl])) {
        return { trimmedUrlWithoutQueryParams: '/', queryParams: null };
    }

    const result = getInternationalizedStaticUrl(trimmedUrlWithoutQueryParams as StaticRewritePathKeyType, domainUrl);
    const queryParams = getQueryWithoutSlugTypeParameterFromQueryString(context.resolvedUrl.split('?')[1]);

    return {
        trimmedUrlWithoutQueryParams: result || trimmedUrlWithoutQueryParams,
        queryParams: queryParams ? `?${queryParams}` : null,
    };
};
