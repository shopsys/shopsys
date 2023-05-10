import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';

export const getLoginUrlWithRedirect = (redirectTargetUrl: string, domainUrl: string): string => {
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], domainUrl);

    const redirectTargetUrlWithoutLeadingSlash =
        redirectTargetUrl[0] === '/' ? redirectTargetUrl.slice(1) : redirectTargetUrl;
    const redirectQuery = redirectTargetUrl.length > 0 ? `?r=${redirectTargetUrlWithoutLeadingSlash}` : '';
    const loginUrlWithRedirect = `${loginUrl}${redirectQuery}`;

    return loginUrlWithRedirect;
};
