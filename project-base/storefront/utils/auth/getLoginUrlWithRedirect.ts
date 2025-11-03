import { GetServerSidePropsContext } from 'next';
import { getBasePathWithLocale } from 'utils/domain/domainUtils';
import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const getLoginUrlWithRedirect = (
    redirectTargetUrl: string,
    domainUrl: string,
    context: GetServerSidePropsContext,
): string => {
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], domainUrl);

    const redirectQuery = redirectTargetUrl.length > 0 ? `?r=${getStringWithoutLeadingSlash(redirectTargetUrl)}` : '';

    return getBasePathWithLocale(`${loginUrl}${redirectQuery}`, context);
};
