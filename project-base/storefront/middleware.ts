import { NextMiddleware } from 'next/server';
import { getBaseUrlWithLocale } from 'utils/domain/domainUtils';
import { getHostAndDomainFromRequest } from 'utils/domain/getHostAndDomainFromRequest';
import { handleDomainRedirect, normalizePathname } from 'utils/middleware/domainHandlers';
import { handleEarlyExitRoutes } from 'utils/middleware/earlyExitHandlers';
import { handleMiddlewareError } from 'utils/middleware/errorHandlers';
import { handleFriendlyUrlResolution, handleSlugTypeQueryParam } from 'utils/middleware/friendlyUrlHandlers';
import { handleStaticRoutes } from 'utils/middleware/staticRouteHandlers';

export const middleware: NextMiddleware = async (request) => {
    try {
        const earlyResponse = handleEarlyExitRoutes(request);

        if (earlyResponse) {
            return earlyResponse;
        }

        const domainInfo = getHostAndDomainFromRequest(request);
        const pathname = normalizePathname(request.nextUrl.pathname);
        const { host, domainId, currentLocale } = domainInfo;
        const { search, origin: baseOrigin } = new URL(request.url);

        const domainRedirectResponse = handleDomainRedirect(domainInfo, pathname, search);

        if (domainRedirectResponse) {
            return domainRedirectResponse;
        }

        const origin = getBaseUrlWithLocale(baseOrigin, currentLocale);

        const staticRouteResponse = handleStaticRoutes(request, host, origin);

        if (staticRouteResponse) {
            return staticRouteResponse;
        }

        const slugTypeResponse = handleSlugTypeQueryParam(search, request.url, currentLocale, pathname);

        if (slugTypeResponse) {
            return slugTypeResponse;
        }

        return await handleFriendlyUrlResolution(pathname, domainId, origin, currentLocale, search, request);
    } catch (e) {
        return handleMiddlewareError(e, request);
    }
};

export const config = {
    matcher: [
        '/', // Explicitly match the homepage
        '/((?!api|_next|favicon.ico|fonts|svg|images|locales|content/locales|icons|grapesjs-template|grapesjs-homepage-article-template|grapesjs-article-template|tailwind-for-admin|robots|security.txt|.well-known).*)',
    ],
};
