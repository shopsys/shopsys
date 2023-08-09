import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getStringWithoutLeadingSlash } from 'helpers/parsing/stringWIthoutSlash';

export const getLoginUrlWithRedirect = (redirectTargetUrl: string, domainUrl: string): string => {
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], domainUrl);

    const redirectQuery = redirectTargetUrl.length > 0 ? `?r=${getStringWithoutLeadingSlash(redirectTargetUrl)}` : '';
    const loginUrlWithRedirect = `${loginUrl}${redirectQuery}`;

    return loginUrlWithRedirect;
};
