import { NextRequest } from 'next/server';

export const isPathnameSegmentDynamic = (segment?: string) => segment?.charAt(0) === ':';

// return un-normalized (original) host
// because "request.nextUrl.origin" would return normalized version ("127.0.0.1:8000" changed to "localhost:3000")
export const getHostFromRequest = (request: NextRequest): string => {
    const host = request.headers.get('Host');

    if (host === null) {
        throw new Error(`Host was not found in the request header.`);
    }

    return host;
};

export const isHomePage = (request: NextRequest) => request.nextUrl.pathname === '/';

export const isInRange = (number: number, start: number, end: number) => number >= start && start <= end;
