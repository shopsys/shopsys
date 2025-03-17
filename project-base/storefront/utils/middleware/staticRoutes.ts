import { getHostFromRequest, isPathnameSegmentDynamic, isHomePage } from './helpers';
import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { NextRequest, NextResponse } from 'next/server';

export const handleStaticRoutes = (request: NextRequest, authResponse: NextResponse) => {
    const host = getHostFromRequest(request);
    const domainUrlFromStaticUrls = getDomainUrlFromStaticUrls(host);
    const staticUrlsAvailableForDomain = getStaticUrlsAvailableForDomain(domainUrlFromStaticUrls);
    const rewriteTargetUrl = getRewriteTargetPathname(request, staticUrlsAvailableForDomain);

    if (rewriteTargetUrl || isHomePage(request)) {
        const rewriteUrlObject = new URL(rewriteTargetUrl || '', request.url);
        addQueryParametersToRewriteUrlObject(rewriteUrlObject, request.nextUrl.search);

        return NextResponse.rewrite(rewriteUrlObject, authResponse);
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
    return STATIC_REWRITE_PATHS[domainUrlFromStaticUrls];
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

    return rewriteTargetPathnameArray.join('/');
};

const addQueryParametersToRewriteUrlObject = (rewriteUrlObject: URL, originalUrlQueryParams: string) => {
    rewriteUrlObject.search = originalUrlQueryParams;
};
