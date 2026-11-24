import { IncomingHttpHeaders } from 'node:http';
import { isFullPageRequest } from 'utils/isFullPageRequest';
import { getUrlWithoutGetParameters } from './getUrlWithoutGetParameters';
import { getStringWithoutLeadingSlash } from './stringWIthoutSlash';

export const getSlugFromServerSideUrl = (originalUrl: string, requestHeaders: IncomingHttpHeaders): string => {
    if (isFullPageRequest(requestHeaders)) {
        return getStringWithoutLeadingSlash(getUrlWithoutGetParameters(originalUrl));
    }

    const lastUrlSegment = originalUrl.split('/').pop()!;
    const beforeExtensionSegment = lastUrlSegment.split('.')[0];
    const strippedSlug = beforeExtensionSegment.split('?')[0];

    return strippedSlug;
};
