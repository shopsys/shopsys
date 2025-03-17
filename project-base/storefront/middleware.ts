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
