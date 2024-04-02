import { getLoginUrlWithRedirect } from './getLoginUrlWithRedirect';
import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { Redirect } from 'next';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

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
