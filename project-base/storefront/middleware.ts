import { handleAuthRedirects, validateAuthTokens } from './utils/middleware/auth';
import { handleFriendlyUrls } from './utils/middleware/friendlyUrls';
import { handleStaticRoutes } from './utils/middleware/staticRoutes';
import { NextMiddleware, NextRequest, NextResponse } from 'next/server';

const ERROR_PAGE_ROUTE = '/404';
const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

export const middleware: NextMiddleware = async (request) => {
    try {
        // Handle authentication redirects if needed
        const authRedirect = handleAuthRedirects(request);
        if (authRedirect) {
            return authRedirect;
        }

        // Validate auth tokens and return the response with potentially refreshed tokens
        const baseResponse = await validateAuthTokens(request);

        // Process static URL rewrites if applicable
        const staticRewriteResponse = handleStaticRoutes(request, baseResponse);
        if (staticRewriteResponse) {
            return staticRewriteResponse;
        }

        // Process friendly URLs
        return await handleFriendlyUrls(request, baseResponse);
    } catch (error) {
        return handleMiddlewareError(error, request);
    }
};

export const config = {
    matcher: [
        '/((?!api|_next|favicon.ico|fonts|svg|images|locales|icons|grapesjs-template|grapesjs-homepage-article-template|grapesjs-article-template|robots).*)',
        '/',
    ],
};

function handleMiddlewareError(error: unknown, request: NextRequest): NextResponse {
    const isDevelopmentMode =
        process.env.ERROR_DEBUGGING_LEVEL === 'console' || process.env.ERROR_DEBUGGING_LEVEL === 'toast-and-console';

    const errorMessage =
        isDevelopmentMode && error instanceof Error ? error.message : 'Middleware runtime error for ' + request.url;

    return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, '500'],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, errorMessage],
        ],
    });
}

// Full examples of what you can access
// request.url                              // https://example.com/path?query=value
// request.nextUrl                          // wrapper: new URL(request.url)
// request.nextUrl.href                     // https://example.com/path?query=value
// request.nextUrl.pathname                 // /path
// request.nextUrl.search                   // ?query=value
// request.nextUrl.searchParams             // URLSearchParams object
// request.nextUrl.searchParams.toString()  // query=value

// good to know - localhost weirdness
// new Headers(request.headers).get('Host') // 127.0.0.1:8000 (real url)
// request.url                              // localhost:3000 (normalized url, why? __NEXT_NO_MIDDLEWARE_URL_NORMALIZE can influence it?)
// request.nextUrl.href                     // localhost:3000 (being born from "request.url")
