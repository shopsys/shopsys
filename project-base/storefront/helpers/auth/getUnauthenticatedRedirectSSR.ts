import { getLoginUrlWithRedirect } from './getLoginUrlWithRedirect';
import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { Redirect } from 'next';

export const getUnauthenticatedRedirectSSR = (resolvedUrl: string, domainUrl: string): { redirect: Redirect } => {
    let redirectTargetUrlWithLeadingSlash = getInternationalizedStaticUrls(['/login'], domainUrl)[0];

    if (resolvedUrl in STATIC_REWRITE_PATHS[domainUrl]) {
        redirectTargetUrlWithLeadingSlash = getInternationalizedStaticUrls(
            [resolvedUrl as StaticRewritePathKeyType],
            domainUrl,
        )[0];
    }

    return {
        redirect: {
            statusCode: 302,
            destination: getLoginUrlWithRedirect(redirectTargetUrlWithLeadingSlash, domainUrl),
        },
    };
};
