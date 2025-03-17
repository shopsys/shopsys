import { NextRequest, NextResponse } from 'next/server';

export const handleAuthRedirect = (request: NextRequest): NextResponse | null => {
    // Redirect to homepage when user is logged in but tries to access auth pages
    const authProtectedPaths = ['/login', '/reset-password', '/registration'];
    const accessToken = request.cookies.get('accessToken')?.value;

    if (accessToken && authProtectedPaths.some((path) => request.nextUrl.pathname.startsWith(path))) {
        const url = request.nextUrl.clone();
        url.pathname = '/';
        return NextResponse.redirect(url);
    }

    return null;
};
