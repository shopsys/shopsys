import { getDomainIdFromHostname } from 'helpers/domain/getDomainIdFromHostname';
import { logException } from 'helpers/errors/logException';
import { NextMiddleware, NextRequest, NextResponse } from 'next/server';
import {
    FriendlyPageTypesValue,
    FriendlyPagesDestinations,
    FriendlyPagesTypes,
    FriendlyPagesTypesKeys,
} from 'types/friendlyUrl';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const STATIC_REWRITE_PATHS = require('config/staticRewritePaths') as Record<string, Record<string, string>>;
const ERROR_PAGE_ROUTE = '/404';

export const middleware: NextMiddleware = async (request) => {
    try {
        const host = getHostFromRequest(request);
        const domainUrlFromStaticUrls = getDomainUrlFromStaticUrls(host);
        const staticUrlsAvailableForDomain = getStaticUrlsAvailableForDomain(domainUrlFromStaticUrls);
        const rewriteTargetUrl = getRewriteTargetPathname(request, staticUrlsAvailableForDomain);

        if (rewriteTargetUrl) {
            const rewriteUrlObject = new URL(rewriteTargetUrl, request.url);
            addQueryParametersToRewriteUrlObject(rewriteUrlObject, request.nextUrl.search);

            return NextResponse.rewrite(rewriteUrlObject);
        }

        const { search } = new URL(request.url);
        const queryParams = new URLSearchParams(search);
        const slugTypeQueryParam = queryParams.get('slugType');

        if (slugTypeQueryParam) {
            return rewriteDynamicPages(slugTypeQueryParam as FriendlyPageTypesValue, request.url, search);
        }

        const pageTypeResponse = await fetch(`${process.env.INTERNAL_ENDPOINT}resolve-friendly-url`, {
            method: 'POST',
            body: JSON.stringify({
                slug: request.nextUrl.pathname,
                domainId: getDomainIdFromHostname(request.headers.get('Host') as string),
            }),
        });

        if (!pageTypeResponse.ok) {
            return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url));
        }

        const pageTypeParsedResponse: { route: FriendlyPageTypesValue; redirectTo: string } =
            await pageTypeResponse.json();

        if (pageTypeParsedResponse.redirectTo && pageTypeParsedResponse.redirectTo !== request.url) {
            return NextResponse.redirect(
                new URL(`${pageTypeParsedResponse.redirectTo}?${queryParams}`, request.url).href,
                301,
            );
        }

        return rewriteDynamicPages(pageTypeParsedResponse.route, request.url, search);
    } catch (e) {
        logException(e);

        return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url));
    }
};

export const config = {
    matcher: [
        '/((?!api|_next|favicon.ico|fonts|svg|images|locales|icons|grapesjs-template|grapesjs-homepage-article-template|grapesjs-article-template).*)',
    ],
};

const rewriteDynamicPages = (pageType: FriendlyPageTypesValue, rewriteUrl: string, queryParams: string) => {
    const pageTypeKey = (Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKeys[]).find(
        (key) => FriendlyPagesTypes[key] === pageType,
    );

    const host = new URL(rewriteUrl).origin;
    const newUrl = new URL(
        pageTypeKey ? `${FriendlyPagesDestinations[pageTypeKey]}${queryParams}` : ERROR_PAGE_ROUTE,
        host,
    );

    return NextResponse.rewrite(newUrl);
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

const addQueryParametersToRewriteUrlObject = (rewriteUrlObject: URL, originalUrlQueryParams: string) => {
    rewriteUrlObject.search = originalUrlQueryParams;
};

const isPathnameSegmentDynamic = (segment?: string) => segment?.charAt(0) === ':';
