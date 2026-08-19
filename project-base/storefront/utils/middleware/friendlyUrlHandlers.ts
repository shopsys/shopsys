import { NextRequest, NextResponse } from 'next/server';
import { FriendlyPagesDestinations, FriendlyPagesTypes, type FriendlyPageTypesValue } from 'types/friendlyUrl';
import { getBaseUrlWithLocale } from 'utils/domain/domainUtils';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { ERROR_PAGE_ROUTE, MIDDLEWARE_STATUS_CODE_KEY, MIDDLEWARE_STATUS_MESSAGE_KEY } from './errorHandlers';

export const handleSlugTypeQueryParam = (
    search: string,
    requestUrl: string,
    currentLocale: string | undefined,
    pathname: string,
): NextResponse | null => {
    const queryParams = new URLSearchParams(search);
    const slugTypeQueryParam = queryParams.get('slugType');

    // the slugType param can be carried over to the error page route when a friendly URL page fails,
    // in which case it must not rewrite the error page back to the dynamic page (e.g. querying slug "404")
    if (isErrorPagePath(pathname)) {
        return null;
    }

    if (slugTypeQueryParam && isFriendlyPageTypesValue(slugTypeQueryParam)) {
        return rewriteDynamicPages(slugTypeQueryParam as FriendlyPageTypesValue, requestUrl, search, currentLocale);
    }

    return null;
};

export const handleFriendlyUrlResolution = async (
    pathname: string,
    domainId: number,
    origin: string,
    currentLocale: string | undefined,
    search: string,
    request: NextRequest,
): Promise<NextResponse> => {
    const pageTypeResponse = await fetch(`${process.env.INTERNAL_ENDPOINT}resolve-friendly-url`, {
        method: 'POST',
        body: JSON.stringify({
            slug: pathname,
            domainId,
        }),
    });

    if (!pageTypeResponse.ok) {
        return createErrorResponse(pageTypeResponse.status, request);
    }

    const pageTypeParsedResponse: { route: FriendlyPageTypesValue; redirectTo: string; redirectCode: number } =
        await pageTypeResponse.json();

    const redirectResponse = handleFriendlyUrlRedirect(pageTypeParsedResponse, origin, currentLocale, search, request);
    if (redirectResponse) {
        return redirectResponse;
    }

    return rewriteDynamicPages(pageTypeParsedResponse.route, request.url, search, currentLocale);
};

const handleFriendlyUrlRedirect = (
    pageTypeParsedResponse: { route: FriendlyPageTypesValue; redirectTo: string; redirectCode: number },
    origin: string,
    currentLocale: string | undefined,
    search: string,
    request: NextRequest,
): NextResponse | null => {
    if (pageTypeParsedResponse.redirectTo && pageTypeParsedResponse.redirectTo !== request.url) {
        const redirectUrl = getBaseUrlWithLocale(origin, currentLocale) + pageTypeParsedResponse.redirectTo + search;
        return NextResponse.redirect(redirectUrl, pageTypeParsedResponse.redirectCode);
    }
    return null;
};

const rewriteDynamicPages = (
    pageType: FriendlyPageTypesValue,
    rewriteUrl: string,
    queryParams: string,
    currentLocale: string | undefined,
): NextResponse => {
    const pageTypeKey = getPageTypeKey(pageType);

    const origin = getBaseUrlWithLocale(new URL(rewriteUrl).origin, currentLocale);

    if (pageTypeKey) {
        return NextResponse.rewrite(new URL(`${origin}${FriendlyPagesDestinations[pageTypeKey]}${queryParams}`));
    }

    return NextResponse.rewrite(new URL(origin + ERROR_PAGE_ROUTE), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, '404'],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, `Friendly URL page not found for ${rewriteUrl}`],
        ],
    });
};

const createErrorResponse = (statusCode: number, request: NextRequest): NextResponse => {
    const is400Error = isInRange(statusCode, 400, 499);
    const is500Error = isInRange(statusCode, 500, 599);

    let statusMessage = `Unknown middleware error for ${request.url}`;

    if (is400Error) {
        statusMessage = `Friendly URL page not found for ${request.url}`;
    } else if (is500Error) {
        statusMessage = `Middleware runtime error for ${request.url}`;
    }

    return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, statusCode.toString()],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, statusMessage],
        ],
    });
};

const isInRange = (number: number, start: number, end: number): boolean => number >= start && number <= end;

// pathname comes normalized without the leading slash and may be prefixed with a locale (e.g. "sk/404")
const isErrorPagePath = (pathname: string): boolean => `/${pathname}`.endsWith(ERROR_PAGE_ROUTE);

function isFriendlyPageTypesValue(value: string): value is FriendlyPageTypesValue {
    return Object.values(FriendlyPagesTypes).includes(value as FriendlyPageTypesValue);
}
