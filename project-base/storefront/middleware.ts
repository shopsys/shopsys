import { logException } from 'helpers/errors/logException';
import { NextMiddleware, NextRequest, NextResponse } from 'next/server';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const STATIC_REWRITE_PATHS = require('config/staticRewritePaths') as Record<string, Record<string, string>>;
const FRIENDLY_URL_PATH = '/[...all]';

export const middleware: NextMiddleware = (request) => {
    try {
        const host = getHostFromRequest(request);
        const domainUrlFromStaticUrls = getDomainUrlFromStaticUrls(host);
        const staticUrlsAvailableForDomain = getStaticUrlsAvailableForDomain(domainUrlFromStaticUrls);
        const rewriteTargetUrl = getRewriteTargetPathname(request, staticUrlsAvailableForDomain);

        if (rewriteTargetUrl !== FRIENDLY_URL_PATH) {
            const rewriteUrlObject = new URL(rewriteTargetUrl, request.url);
            addQueryParametersToRewriteUrlObject(rewriteUrlObject, request.nextUrl.search);

            return NextResponse.rewrite(rewriteUrlObject);
        }

        return NextResponse.next();
    } catch (e) {
        logException(e);

        return NextResponse.rewrite(new URL(FRIENDLY_URL_PATH, request.url));
    }
};

export const config = {
    matcher: ['/((?!api|_next/static|favicon.ico|fonts|images|locales|icons).*)'],
};

const getHostFromRequest = (request: NextRequest): string => {
    const requestHeaders = new Headers(request.headers);
    const host = requestHeaders.get('host');

    if (host === null) {
        throw new Error(`Host was not found in the request header.`);
    }

    return host;
};

const getDomainUrlFromStaticUrls = (host: string): string => {
    const domainUrlFromStaticUrls = Object.keys(STATIC_REWRITE_PATHS).find((domainUrl) => domainUrl.match(host));

    if (domainUrlFromStaticUrls === undefined) {
        throw new Error(`Host ${host} does not have a corresponding URL in the available static URLS.`);
    }

    return domainUrlFromStaticUrls;
};

const getStaticUrlsAvailableForDomain = (domainUrlFromStaticUrls: string): Record<string, string> => {
    const staticUrlsAvailableForDomain = STATIC_REWRITE_PATHS[domainUrlFromStaticUrls];

    return staticUrlsAvailableForDomain;
};

const getRewriteTargetPathname = (
    request: NextRequest,
    staticUrlsAvailableForDomain: Record<string, string>,
): string => {
    let rewriteTargetPathnameArray: string[] = [];

    for (const [staticRewritePathname, staticLocalizedPathname] of Object.entries(staticUrlsAvailableForDomain)) {
        const requestedPathnameSegments = request.nextUrl.pathname.split('/');
        const staticRewritePathnameSegments = staticRewritePathname.split('/');
        const staticLocalizedPathnameSegments = staticLocalizedPathname.split('/');

        let areAllSegmentsIdenticalOrDynamic = true;
        const rewriteTargetPathnameArrayBuffer = [];

        for (let index = 0; index < requestedPathnameSegments.length; index++) {
            const isCurrentPathnameSegmentDynamic = isPathnameSegmentDynamic(staticRewritePathnameSegments[index]);

            areAllSegmentsIdenticalOrDynamic =
                areAllSegmentsIdenticalOrDynamic &&
                (staticLocalizedPathnameSegments[index] === requestedPathnameSegments[index] ||
                    isCurrentPathnameSegmentDynamic);

            if (isCurrentPathnameSegmentDynamic) {
                rewriteTargetPathnameArrayBuffer.push(requestedPathnameSegments[index]);
            } else {
                rewriteTargetPathnameArrayBuffer.push(staticRewritePathnameSegments[index]);
            }
        }

        if (areAllSegmentsIdenticalOrDynamic) {
            rewriteTargetPathnameArray = [...rewriteTargetPathnameArrayBuffer];
        }
    }

    const rewriteTargetPathname = rewriteTargetPathnameArray.join('/');

    return rewriteTargetPathname;
};

const addQueryParametersToRewriteUrlObject = (rewriteUrlObject: URL, originalUrlQueryParams: string) => {
    rewriteUrlObject.search = originalUrlQueryParams;
};

const isPathnameSegmentDynamic = (segment?: string) => segment?.charAt(0) === ':';
