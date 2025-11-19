import { getHostFromRequest, isHomePage } from './helpers';
import { STATIC_REWRITE_PATHS } from 'config/staticRewritePaths';
import { NextRequest, NextResponse } from 'next/server';
import { getHostAndDomainFromRequest } from 'utils/domain/getHostAndDomainFromRequest';

export const handleStaticRoutes = (request: NextRequest, previousResponse: NextResponse) => {
    // Resolve domain and pass to app router via headers
    const { domainId, host, redirect } = getHostAndDomainFromRequest(request);

    // Handle redirect when accessing domain base URL that needs redirect
    if (redirect) {
        const normalizedHost = host.endsWith('/') ? host.slice(0, -1) : host;
        const targetUrl = new URL(normalizedHost);
        targetUrl.search = request.nextUrl.search;

        return NextResponse.redirect(targetUrl, {
            status: 302,
            headers: previousResponse.headers,
        });
    }

    previousResponse.headers.set('x-domain-id', domainId.toString());
    previousResponse.headers.set('x-domain-url', host);

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

    const routes = STATIC_REWRITE_PATHS[domainUrlKey];

    return Object.fromEntries(Object.entries(routes)) as Record<string, string>;
};

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
