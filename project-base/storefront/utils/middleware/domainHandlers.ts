import { NextResponse } from 'next/server';
import { getHostAndDomainFromRequest } from 'utils/domain/getHostAndDomainFromRequest';

export const normalizePathname = (pathname: string): string => {
    if (pathname.startsWith('/')) {
        return pathname.substring(1);
    }
    return pathname;
};

export const handleDomainRedirect = (
    domainInfo: ReturnType<typeof getHostAndDomainFromRequest>,
    pathname: string,
    search: string,
): NextResponse | null => {
    if (domainInfo.redirect) {
        const redirectUrl = new URL(`${domainInfo.host}${pathname}${search}`);
        return NextResponse.redirect(redirectUrl, 308);
    }
    return null;
};
