import { getInternationalizedStaticUrl } from './getInternationalizedStaticUrl';
import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { getQueryWithoutSlugTypeParameterFromQueryString } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromQueryString';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { GetServerSidePropsContext, NextPageContext } from 'next';

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
