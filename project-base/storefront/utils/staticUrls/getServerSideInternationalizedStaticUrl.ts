import { getInternationalizedStaticUrl } from './getInternationalizedStaticUrl';
import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { GetServerSidePropsContext, NextPageContext } from 'next';
import { getQueryWithoutSlugTypeParameterFromQueryString } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromQueryString';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

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

    const internationalizedStaticUrl = getInternationalizedStaticUrl(
        trimmedUrlWithoutQueryParams as StaticRewritePathKeyType,
        domainUrl,
    );
    const queryParams = getQueryWithoutSlugTypeParameterFromQueryString(context.resolvedUrl.split('?')[1]);

    return {
        trimmedUrlWithoutQueryParams: internationalizedStaticUrl || trimmedUrlWithoutQueryParams,
        queryParams: queryParams ? `?${queryParams}` : null,
    };
};
