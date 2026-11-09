import { IncomingMessage } from 'node:http';
import { parse as parseCookie } from 'cookie';
import { getCookies as getClientCookies } from 'cookies-next/client';

// URQL's authentication callbacks need synchronous reads, including during Pages Router SSR.
export const getCookies = (context?: { req?: IncomingMessage }): Record<string, string | undefined> => {
    if (context?.req) {
        const request = context.req as IncomingMessage & { cookies?: Record<string, string | undefined> };

        return request.cookies ?? parseCookie(request.headers.cookie ?? '');
    }

    return typeof window === 'undefined' ? {} : (getClientCookies() ?? {});
};
