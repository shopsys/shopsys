import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { NextMiddleware, NextRequest, NextResponse } from 'next/server';
import { type FriendlyPageTypesValue, FriendlyPagesDestinations, FriendlyPagesTypes } from 'types/friendlyUrl';
import { getBaseUrlWithLocale } from 'utils/domain/domainUtils';
import { getHostAndDomainFromRequest } from 'utils/domain/getHostAndDomainFromRequest';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';

const ERROR_PAGE_ROUTE = '/404';
const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

export const middleware: NextMiddleware = async (request) => {
    try {
        if (request.nextUrl.pathname === '/.well-known/appspecific/com.chrome.devtools.json') {
            return new NextResponse(null, { status: 204 });
        }

        if (request.url.includes('_next/data')) {
            return new NextResponse(null, { status: 404 });
        }

        const domainInfo = getHostAndDomainFromRequest(request);
        const { search, origin: baseOrigin } = new URL(request.url);

        let pathname = request.nextUrl.pathname;
        if (pathname.startsWith('/')) {
            pathname = pathname.substring(1);
        }

        // Handle redirect if domain couldn't be resolved
        if (domainInfo.redirect) {
            const redirectUrl = new URL(`${domainInfo.host}${pathname}${search}`);
            return NextResponse.redirect(redirectUrl, 308);
        }

        const { host, domainId, currentLocale } = domainInfo;

        const origin = getBaseUrlWithLocale(baseOrigin, currentLocale);
        const domainUrlFromStaticUrls = getDomainUrlFromStaticUrls(host);
        const staticUrlsAvailableForDomain = getStaticUrlsAvailableForDomain(domainUrlFromStaticUrls);
        const rewriteTargetUrl = getRewriteTargetPathname(request, staticUrlsAvailableForDomain);

        if (rewriteTargetUrl) {
            const rewriteUrlObject = new URL(`${origin}${rewriteTargetUrl}`);

            addQueryParametersToRewriteUrlObject(rewriteUrlObject, request.nextUrl.search);

            return NextResponse.rewrite(rewriteUrlObject);
        }

        const queryParams = new URLSearchParams(search);
        const slugTypeQueryParam = queryParams.get('slugType');

        if (slugTypeQueryParam && isFriendlyPageTypesValue(slugTypeQueryParam)) {
            return rewriteDynamicPages(
                slugTypeQueryParam as FriendlyPageTypesValue,
                request.url,
                search,
                currentLocale,
            );
        }

        const pageTypeResponse = await fetch(`${process.env.INTERNAL_ENDPOINT}resolve-friendly-url`, {
            method: 'POST',
            body: JSON.stringify({
                slug: pathname,
                domainId,
            }),
        });

        if (!pageTypeResponse.ok) {
            const is400Error = isInRange(pageTypeResponse.status, 400, 499);
            const is500Error = isInRange(pageTypeResponse.status, 500, 599);

            let statusMessage = 'Unknown middleware error for ' + request.url;
            if (is400Error) {
                statusMessage = 'Friendly URL page not found for ' + request.url;
            } else if (is500Error) {
                statusMessage = 'Middleware runtime error for ' + request.url;
            }

            return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
                headers: [
                    [MIDDLEWARE_STATUS_CODE_KEY, pageTypeResponse.status.toString()],
                    [MIDDLEWARE_STATUS_MESSAGE_KEY, statusMessage],
                ],
            });
        }

        const pageTypeParsedResponse: { route: FriendlyPageTypesValue; redirectTo: string; redirectCode: number } =
            await pageTypeResponse.json();

        if (pageTypeParsedResponse.redirectTo && pageTypeParsedResponse.redirectTo !== request.url) {
            const redirectUrl =
                getBaseUrlWithLocale(origin, currentLocale) + pageTypeParsedResponse.redirectTo + search;
            return NextResponse.redirect(redirectUrl, pageTypeParsedResponse.redirectCode);
        }

        return rewriteDynamicPages(pageTypeParsedResponse.route, request.url, search, currentLocale);
    } catch (e) {
        if (
            (process.env.ERROR_DEBUGGING_LEVEL === 'console' ||
                process.env.ERROR_DEBUGGING_LEVEL === 'toast-and-console') &&
            e instanceof Error
        ) {
            return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
                headers: [
                    [MIDDLEWARE_STATUS_CODE_KEY, '500'],
                    [MIDDLEWARE_STATUS_MESSAGE_KEY, e.message],
                ],
            });
        }

        return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
            headers: [
                [MIDDLEWARE_STATUS_CODE_KEY, '500'],
                [MIDDLEWARE_STATUS_MESSAGE_KEY, 'Middleware runtime error for ' + request.url],
            ],
        });
    }
};

export const config = {
    matcher: [
        '/', // Explicitly match the homepage
        '/((?!api|_next|favicon.ico|fonts|svg|images|locales|content/locales|icons|grapesjs-template|grapesjs-homepage-article-template|grapesjs-article-template|tailwind-for-admin|robots).*)',
    ],
};

const isInRange = (number: number, start: number, end: number) => number >= start && start <= end;

const rewriteDynamicPages = (
    pageType: FriendlyPageTypesValue,
    rewriteUrl: string,
    queryParams: string,
    currentLocale: string | undefined,
) => {
    const pageTypeKey = getPageTypeKey(pageType);

    const origin = getBaseUrlWithLocale(new URL(rewriteUrl).origin, currentLocale);

    if (pageTypeKey) {
        return NextResponse.rewrite(new URL(`${origin}${FriendlyPagesDestinations[pageTypeKey]}${queryParams}`));
    }

    return NextResponse.rewrite(new URL(origin + ERROR_PAGE_ROUTE), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, '404'],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, 'Friendly URL page not found for ' + rewriteUrl],
        ],
    });
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

function isFriendlyPageTypesValue(value: string): value is FriendlyPageTypesValue {
    return Object.values(FriendlyPagesTypes).includes(value as FriendlyPageTypesValue);
}
