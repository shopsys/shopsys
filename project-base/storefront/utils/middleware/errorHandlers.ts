import { NextRequest, NextResponse } from 'next/server';

export const ERROR_PAGE_ROUTE = '/404';
export const MIDDLEWARE_STATUS_CODE_KEY = 'middleware-status-code';
export const MIDDLEWARE_STATUS_MESSAGE_KEY = 'middleware-status-message';

export const handleMiddlewareError = (error: unknown, request: NextRequest): NextResponse => {
    if (
        (process.env.ERROR_DEBUGGING_LEVEL === 'console' ||
            process.env.ERROR_DEBUGGING_LEVEL === 'toast-and-console') &&
        error instanceof Error
    ) {
        return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
            headers: [
                [MIDDLEWARE_STATUS_CODE_KEY, '500'],
                [MIDDLEWARE_STATUS_MESSAGE_KEY, error.message],
            ],
        });
    }

    return NextResponse.rewrite(new URL(ERROR_PAGE_ROUTE, request.url), {
        headers: [
            [MIDDLEWARE_STATUS_CODE_KEY, '500'],
            [MIDDLEWARE_STATUS_MESSAGE_KEY, `Middleware runtime error for ${request.url}`],
        ],
    });
};
