import { getLoginUrlWithRedirect } from './getLoginUrlWithRedirect';
import { STATIC_REWRITE_PATHS, StaticRewritePathKeyType } from 'config/staticRewritePaths';
import { Redirect } from 'next';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const getUnauthorizedRedirectSSR = (resolvedUrl: string, domainUrl: string): { redirect: Redirect } => {
    let redirectTargetUrlWithLeadingSlash = getInternationalizedStaticUrls(['/customer'], domainUrl)[0];

    if (resolvedUrl in STATIC_REWRITE_PATHS[domainUrl]) {
        redirectTargetUrlWithLeadingSlash = getInternationalizedStaticUrls(
            [resolvedUrl as StaticRewritePathKeyType],
            domainUrl,
        )[0];
    }

    return {
        redirect: {
            statusCode: 403,
            destination: getLoginUrlWithRedirect(redirectTargetUrlWithLeadingSlash, domainUrl),
        },
    };
};
