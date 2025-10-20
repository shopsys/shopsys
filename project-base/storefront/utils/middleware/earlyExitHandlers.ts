import { NextRequest, NextResponse } from 'next/server';

export const handleEarlyExitRoutes = (request: NextRequest): NextResponse | null => {
    if (request.nextUrl.pathname === '/.well-known/appspecific/com.chrome.devtools.json') {
        return new NextResponse(null, { status: 204 });
    }

    if (request.url.includes('_next/data')) {
        return new NextResponse(null, { status: 404 });
    }

    return null;
};
