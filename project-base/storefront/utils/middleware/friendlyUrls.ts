import { getHostFromRequest } from './helpers';
import { NextRequest, NextResponse } from 'next/server';
import {
    FriendlyPagesDestinations,
    FriendlyPagesTypes,
    FriendlyPagesTypesKey,
    FriendlyPageTypesValue,
} from 'types/friendlyUrl';
import { getDomainIdFromHostname } from 'utils/domain/getDomainIdFromHostname';

export const handleFriendlyUrls = async (
    request: NextRequest,
    previousResponse: NextResponse,
): Promise<NextResponse> => {
    // If slugType already exists in query params, handle directly
    const slugTypeQueryParam = request.nextUrl.searchParams.get('slugType');
    if (slugTypeQueryParam) {
        return rewriteDynamicPages(slugTypeQueryParam as FriendlyPageTypesValue, request, previousResponse);
    }

    return await resolveFriendlyUrl(request, previousResponse);
};

async function resolveFriendlyUrl(request: NextRequest, previousResponse: NextResponse): Promise<NextResponse> {
    const pageTypeResponse = await fetch(`${process.env.INTERNAL_ENDPOINT}resolve-friendly-url`, {
        method: 'POST',
        body: JSON.stringify({
            slug: request.nextUrl.pathname,
            domainId: getDomainIdFromHostname(getHostFromRequest(request)),
        }),
    });

    if (!pageTypeResponse.ok) {
        const error = new Error(`Friendly URL resolution failed with status: ${pageTypeResponse.status}`);
        (error as any).statusCode = pageTypeResponse.status;
        (error as any).friendlyUrl = true;
        (error as any).request = request;
        throw error;
    }

    const parsedResponse = (await pageTypeResponse.json()) as {
        route: FriendlyPageTypesValue;
        redirectTo: string;
        redirectCode: number;
    };

    // Handle redirects if php-webserver decides
    if (parsedResponse.redirectTo && parsedResponse.redirectTo !== request.url) {
        const newUrl = request.nextUrl.clone();
        newUrl.pathname = parsedResponse.redirectTo;
        return NextResponse.redirect(newUrl, {
            status: parsedResponse.redirectCode,
            headers: previousResponse.headers,
        });
    }

    // Use the route from the response to rewrite the URL
    return rewriteDynamicPages(parsedResponse.route, request, previousResponse);
}

function rewriteDynamicPages(pageType: FriendlyPageTypesValue, request: NextRequest, previousResponse: NextResponse) {
    const pageTypeKey = (Object.keys(FriendlyPagesTypes) as FriendlyPagesTypesKey[]).find(
        (key) => FriendlyPagesTypes[key] === pageType,
    );

    if (!pageTypeKey) {
        const error = new Error('Friendly URL page not found for ' + request.url);
        (error as any).statusCode = 404;
        (error as any).friendlyUrl = true;
        (error as any).request = request;
        throw error;
    }

    const friendlyPrefix = FriendlyPagesDestinations[pageTypeKey]; // should always begin with '/'
    const friendlyPathname = friendlyPrefix + request.nextUrl.pathname;

    previousResponse.headers.set('x-pathname', friendlyPrefix);
    previousResponse.headers.set('x-asPath', request.nextUrl.origin + friendlyPathname);
    const newUrl = request.nextUrl.clone();
    newUrl.pathname = friendlyPrefix + request.nextUrl.pathname;
    return NextResponse.rewrite(newUrl, previousResponse);
}
