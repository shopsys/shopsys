import { NextRequest } from 'next/server';

export const isPathnameSegmentDynamic = (segment?: string) => segment?.charAt(0) === ':';

export const getHostFromRequest = (request: NextRequest): string => {
    const requestHeaders = new Headers(request.headers);
    const host = requestHeaders.get('host');

    if (host === null) {
        throw new Error(`Host was not found in the request header.`);
    }

    return host;
};

export const isHomePage = (request: NextRequest) => request.nextUrl.pathname === '/';

export const isInRange = (number: number, start: number, end: number) => number >= start && start <= end;
