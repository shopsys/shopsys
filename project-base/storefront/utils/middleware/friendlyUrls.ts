import { getHostFromRequest, isInRange } from './helpers';
import { NextRequest, NextResponse } from 'next/server';
import {
    FriendlyPagesDestinations,
    FriendlyPagesTypes,
    FriendlyPagesTypesKey,
    FriendlyPageTypesValue,
} from 'types/friendlyUrl';
import { getDomainIdFromHostname } from 'utils/domain/getDomainIdFromHostname';

const ERROR_PAGE_ROUTE = '/404';
const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

export const handleFriendlyUrls = async (request: NextRequest, baseResponse: NextResponse): Promise<NextResponse> => {
    const { search } = new URL(request.url);
    const queryParams = new URLSearchParams(search);

    // If slugType already exists in query params, handle directly
    const slugTypeQueryParam = queryParams.get('slugType');
    if (slugTypeQueryParam) {
        return rewriteDynamicPages(slugTypeQueryParam as FriendlyPageTypesValue, request);
    }

    // Otherwise, resolve the friendly URL
    return await resolveFriendlyUrl(request, search, baseResponse);
};

async function resolveFriendlyUrl(
    request: NextRequest,
    search: string,
    baseResponse: NextResponse,
): Promise<NextResponse> {
    const pageTypeResponse = await fetch(`${process.env.INTERNAL_ENDPOINT}resolve-friendly-url`, {
        method: 'POST',
        body: JSON.stringify({
            slug: request.nextUrl.pathname,
            domainId: getDomainIdFromHostname(getHostFromRequest(request)),
        }),
    });

    if (!pageTypeResponse.ok) {
        return handleFriendlyUrlError(pageTypeResponse, request, baseResponse);
    }

    const parsedResponse = (await pageTypeResponse.json()) as {
        route: FriendlyPageTypesValue;
        redirectTo: string;
        redirectCode: number;
    };

    // Handle redirects if needed
    if (parsedResponse.redirectTo && parsedResponse.redirectTo !== request.url) {
        return handleRedirect(parsedResponse, request, baseResponse);
    }

    // Use the route from the response to rewrite the URL
    return rewriteDynamicPages(parsedResponse.route, request);
}

function rewriteDynamicPages(pageType: FriendlyPageTypesValue, request: NextRequest) {
    const pageTypeKey = (Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKey[]).find(
        (key) => FriendlyPagesTypes[key] === pageType,
    );

    if (!pageTypeKey) {
        return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
            headers: [
                [MIDDLEWARE_STATUS_CODE_KEY, '404'],
                [MIDDLEWARE_STATUS_MESSAGE_KEY, 'Friendly URL page not found for ' + request.url],
            ],
        });
    }

    const lastUrlSegment = request.nextUrl.pathname.split('/').pop(); // will work as long as only last (pop) part is needed
    const lastUrlSegmentWithSearch = `/${lastUrlSegment}${request.nextUrl.search}`;

    return NextResponse.rewrite(
        new URL(`${FriendlyPagesDestinations[pageTypeKey]}${lastUrlSegmentWithSearch}`, request.url),
        {
            headers: [
                ['x-pathname', FriendlyPagesDestinations[pageTypeKey]],
                ['x-asPath', lastUrlSegmentWithSearch],
            ],
        },
    );
}

function handleFriendlyUrlError(
    pageTypeResponse: Response,
    request: NextRequest,
    baseResponse: NextResponse,
): NextResponse {
    const statusCode = pageTypeResponse.status;
    const is400Error = isInRange(statusCode, 400, 499);
    const is500Error = isInRange(statusCode, 500, 599);

    let statusMessage = 'Unknown middleware error for ' + request.url;

    if (is400Error) {
        statusMessage = 'Friendly URL page not found for ' + request.url;
    } else if (is500Error) {
        statusMessage = 'Middleware runtime error for ' + request.url;
    }

    return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
        ...baseResponse,
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, statusCode.toString()],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, statusMessage],
        ],
    });
}

function handleRedirect(
    parsedResponse: { redirectTo: string; redirectCode: number },
    request: NextRequest,
    baseResponse: NextResponse,
): NextResponse {
    const redirectUrl = new URL(`${parsedResponse.redirectTo}${request.nextUrl.search}`, request.url).href;

    return NextResponse.redirect(redirectUrl, {
        ...baseResponse,
        status: parsedResponse.redirectCode,
    });
}
