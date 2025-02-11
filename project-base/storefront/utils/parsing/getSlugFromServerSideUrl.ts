import { getUrlWithoutGetParameters } from './getUrlWithoutGetParameters';
import { getStringWithoutLeadingSlash } from './stringWIthoutSlash';
import { NextIncomingMessage } from 'next/dist/server/request-meta';
import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr';

export const getSlugFromServerSideUrl = (
    originalUrl: string,
    requestHeaders: NextIncomingMessage['headers'],
): string => {
    if (getIsRedirectedFromSsr(requestHeaders)) {
        return getStringWithoutLeadingSlash(getUrlWithoutGetParameters(originalUrl));
    }

    const lastUrlSegment = originalUrl.split('/').pop()!;
    const beforeExtensionSegment = lastUrlSegment.split('.')[0];
    const strippedSlug = beforeExtensionSegment.split('?')[0];

    return strippedSlug;
};
