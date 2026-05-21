import type { NextRequest } from 'next/server';

type ParsedRequest = {
    requestBaseUrl: string;
    requestPath: string;
    acceptLanguage: string;
};

const getFirstHeaderValue = (headerValue: string | null): string | undefined => {
    return headerValue?.split(',')[0]?.trim() || undefined;
};

export const parseRequest = (request: NextRequest): ParsedRequest => {
    const requestHeaders = new Headers(request.headers);
    const requestHost = getFirstHeaderValue(requestHeaders.get('x-forwarded-host')) ?? requestHeaders.get('host');

    if (!requestHost) {
        throw new Error('Host was not found in the request header.');
    }

    // Use original URL pathname instead of nextUrl.pathname to avoid locale stripping
    const originalUrl = new URL(request.url);
    const protocol =
        getFirstHeaderValue(requestHeaders.get('x-forwarded-proto')) ?? originalUrl.protocol.replace(':', '');
    const requestBaseUrl = `${protocol}://${requestHost}`;
    const requestPath = originalUrl.pathname;
    const acceptLanguage = requestHeaders.get('accept-language') || '';

    return {
        requestBaseUrl,
        requestPath,
        acceptLanguage,
    };
};
