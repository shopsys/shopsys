import { getHostFromRequest, isPathnameSegmentDynamic, isHomePage } from './helpers';
import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { NextRequest, NextResponse } from 'next/server';

export const handleStaticRoutes = (request: NextRequest, previousResponse: NextResponse) => {
    // early return for homepage
    if (isHomePage(request)) {
        previousResponse.headers.set('x-pathname', '/');
        previousResponse.headers.set('x-asPath', request.nextUrl.origin);
        // no need to rewrite url, we are already at '/'
        return previousResponse;
    }

    const rewriteTargetUrl = getRewriteTargetPathname(
        request,
        getStaticUrlsAvailableForDomain(getHostFromRequest(request)),
    );

    if (rewriteTargetUrl) {
        previousResponse.headers.set('x-pathname', rewriteTargetUrl);
        previousResponse.headers.set('x-asPath', request.nextUrl.origin + rewriteTargetUrl);
        const newUrl = request.nextUrl.clone();
        newUrl.pathname = rewriteTargetUrl;
        return NextResponse.rewrite(newUrl, previousResponse);
    }

    return null;
};

const getStaticUrlsAvailableForDomain = (host: string): Record<string, string> => {
    const domainUrlKey = Object.keys(STATIC_REWRITE_PATHS).find((domainUrl) => domainUrl.match(host));

    if (domainUrlKey === undefined) {
        throw new Error(`Host ${host} does not have a corresponding URL in the available static URLS.`);
    }

    return STATIC_REWRITE_PATHS[domainUrlKey];
};

// const getRewriteTargetPathname = (
//     request: NextRequest,
//     staticUrlsAvailableForDomain: Record<string, string>,
// ): string => {
//     let rewriteTargetPathnameArray: string[] = [];

//     for (const [staticRewritePathname, staticLocalizedPathname] of Object.entries(staticUrlsAvailableForDomain)) {
//         const requestedPathnameSegments = request.nextUrl.pathname.split('/');
//         const staticRewritePathnameSegments = staticRewritePathname.split('/');
//         const staticLocalizedPathnameSegments = staticLocalizedPathname.split('/');

//         let areAllSegmentsIdenticalOrDynamic = true;
//         const rewriteTargetPathnameArrayBuffer = [];
//         const hasDynamicSegment = staticRewritePathnameSegments.some((segment) => isPathnameSegmentDynamic(segment));

//         for (let index = 0; index < requestedPathnameSegments.length; index++) {
//             const isCurrentPathnameSegmentDynamic = isPathnameSegmentDynamic(staticRewritePathnameSegments[index]);

//             areAllSegmentsIdenticalOrDynamic =
//                 areAllSegmentsIdenticalOrDynamic &&
//                 (staticLocalizedPathnameSegments[index] === requestedPathnameSegments[index] ||
//                     isCurrentPathnameSegmentDynamic);

//             if (isCurrentPathnameSegmentDynamic) {
//                 rewriteTargetPathnameArrayBuffer.push(requestedPathnameSegments[index]);
//             } else {
//                 rewriteTargetPathnameArrayBuffer.push(staticRewritePathnameSegments[index]);
//             }
//         }

//         if (hasDynamicSegment && requestedPathnameSegments.length !== staticRewritePathnameSegments.length) {
//             areAllSegmentsIdenticalOrDynamic = false;
//         }

//         if (areAllSegmentsIdenticalOrDynamic) {
//             rewriteTargetPathnameArray = [...rewriteTargetPathnameArrayBuffer];
//         }
//     }

//     return rewriteTargetPathnameArray.join('/'); // returns empty string if rewriteTargetPathnameArray is empty
// };

function getRewriteTargetPathname(request: NextRequest, routeDefinitions: Record<string, string>): string {
    const requestedSegments = request.nextUrl.pathname.split('/').filter(Boolean);

    for (const [leftSide, rightSide] of Object.entries(routeDefinitions)) {
        const rightSegments = rightSide.split('/').filter(Boolean);

        if (rightSegments.length !== requestedSegments.length) {
            continue; // Skip if segment count doesn't match
        }

        const params: Record<string, string> = {};
        let isMatch = true;

        for (let i = 0; i < rightSegments.length; i++) {
            if (rightSegments[i].startsWith(':')) {
                params[rightSegments[i]] = requestedSegments[i]; // Store dynamic value
            } else if (rightSegments[i] !== requestedSegments[i]) {
                isMatch = false;
                break;
            }
        }

        if (isMatch) {
            return leftSide.replace(/:(\w+)/g, (_, param) => params[':' + param] || '');
        }
    }

    return ''; // No match found
}