import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { Redirect } from 'next';

export const getUnauthenticatedRedirectSSR = (resolvedUrl: string, domainUrl: string): { redirect: Redirect } => {
    const [loginUrl, redirectTargetUrlWithLeadingSlash] = getInternationalizedStaticUrls(
        ['/login', resolvedUrl],
        domainUrl,
    );
    const redirectTargetUrl = redirectTargetUrlWithLeadingSlash.slice(1);
    const redirectQuery = redirectTargetUrl.length > 0 ? `?r=${redirectTargetUrl}` : '';
    const logginUrlWithRedirect = `${loginUrl}${redirectQuery}`;

    return {
        redirect: {
            statusCode: 302,
            destination: logginUrlWithRedirect,
        },
    };
};
