import { getLoginUrlWithRedirect } from './getLoginUrlWithRedirect';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { Redirect } from 'next';

export const getUnauthenticatedRedirectSSR = (resolvedUrl: string, domainUrl: string): { redirect: Redirect } => {
    const [redirectTargetUrlWithLeadingSlash] = getInternationalizedStaticUrls([resolvedUrl], domainUrl);

    return {
        redirect: {
            statusCode: 302,
            destination: getLoginUrlWithRedirect(redirectTargetUrlWithLeadingSlash, domainUrl),
        },
    };
};
