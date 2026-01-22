import type { NextRequest } from 'next/server';

type ParsedRequest = {
    requestBaseUrl: string;
    requestPath: string;
    acceptLanguage: string;
};

export const parseRequest = (request: NextRequest): ParsedRequest => {
    const requestHeaders = new Headers(request.headers);
    const requestHost = requestHeaders.get('host');

    if (!requestHost) {
        throw new Error('Host was not found in the request header.');
    }

    const protocol = requestHeaders.get('x-forwarded-proto') || 'http';
    const requestBaseUrl = `${protocol}://${requestHost}`;

    // Use original URL pathname instead of nextUrl.pathname to avoid locale stripping
    const originalUrl = new URL(request.url);
    const requestPath = originalUrl.pathname;
    const acceptLanguage = requestHeaders.get('accept-language') || '';

    return {
        requestBaseUrl,
        requestPath,
        acceptLanguage,
    };
};
