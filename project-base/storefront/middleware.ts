import { validateAuthTokens } from './utils/middleware/auth';
import { handleAuthRedirect } from './utils/middleware/authRedirect';
import { handleFriendlyUrls } from './utils/middleware/friendlyUrls';
import { isInRange } from './utils/middleware/helpers';
import { handleStaticRoutes } from './utils/middleware/staticRoutes';
import { NextMiddleware, NextRequest, NextResponse } from 'next/server';

const ERROR_PAGE_ROUTE = '/404';
const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

// TODO: ❗❗❗ check latest middleware from PAGES ❗❗❗
export const middleware: NextMiddleware = async (request) => {
    try {
        // Handle authentication redirects if needed
        const authRedirect = handleAuthRedirect(request);
        if (authRedirect) {
            return authRedirect;
        }

        // Validate auth tokens and return the response with potentially refreshed tokens
        const validTokensResponse = await validateAuthTokens(request);

        // Process static URL rewrites if applicable
        const staticResponse = handleStaticRoutes(request, validTokensResponse);
        if (staticResponse) {
            return staticResponse;
        }

        // Process friendly URLs
        return await handleFriendlyUrls(request, validTokensResponse);
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

    const isFriendlyUrlError = (error as any)?.friendlyUrl === true;
    const statusCode = (error as any)?.statusCode || 500;

    let statusMessage;
    if (isFriendlyUrlError) {
        const is400Error = isInRange(statusCode, 400, 499);
        const is500Error = isInRange(statusCode, 500, 599);

        if (is400Error) {
            statusMessage = 'Friendly URL page not found for ' + request.url;
        } else if (is500Error) {
            statusMessage = 'Middleware runtime error for ' + request.url;
        } else {
            statusMessage = 'Unknown middleware error for ' + request.url;
        }
    } else {
        // Handle non-friendly URL errors as before
        statusMessage =
            isDevelopmentMode && error instanceof Error ? error.message : 'Middleware runtime error for ' + request.url;
    }

    return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, statusCode.toString()],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, statusMessage],
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
