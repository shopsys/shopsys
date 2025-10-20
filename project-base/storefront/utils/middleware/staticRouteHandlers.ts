import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { NextRequest, NextResponse } from 'next/server';

export const handleStaticRoutes = (request: NextRequest, host: string, origin: string): NextResponse | null => {
    const domainUrlFromStaticUrls = getDomainUrlFromStaticUrls(host);
    const staticUrlsAvailableForDomain = getStaticUrlsAvailableForDomain(domainUrlFromStaticUrls);
    const rewriteTargetUrl = getRewriteTargetPathname(request, staticUrlsAvailableForDomain);

    if (rewriteTargetUrl) {
        const rewriteUrlObject = new URL(`${origin}${rewriteTargetUrl}`);
        addQueryParametersToRewriteUrlObject(rewriteUrlObject, request.nextUrl.search);
        return NextResponse.rewrite(rewriteUrlObject);
    }

    return null;
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
        const hasDynamicSegment = staticRewritePathnameSegments.some((segment) => isPathnameSegmentDynamic(segment));

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

        if (hasDynamicSegment && requestedPathnameSegments.length !== staticRewritePathnameSegments.length) {
            areAllSegmentsIdenticalOrDynamic = false;
        }

        if (areAllSegmentsIdenticalOrDynamic) {
            rewriteTargetPathnameArray = [...rewriteTargetPathnameArrayBuffer];
        }
    }

    const rewriteTargetPathname = rewriteTargetPathnameArray.join('/');

    return rewriteTargetPathname;
};

const addQueryParametersToRewriteUrlObject = (rewriteUrlObject: URL, originalUrlQueryParams: string): void => {
    rewriteUrlObject.search = originalUrlQueryParams;
};

const isPathnameSegmentDynamic = (segment?: string): boolean => segment?.charAt(0) === ':';
