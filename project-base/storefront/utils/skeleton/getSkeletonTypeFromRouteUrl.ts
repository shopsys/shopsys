import type { PageType } from 'store/slices/createPageLoadingStateSlice';
import { FriendlyPagesDestinations, type FriendlyPageTypesValue } from 'types/friendlyUrl';
import { SkeletonEnum } from 'types/skeletons';
import { getPageTypeKey } from 'utils/page/getPageTypeKey';
import { SLUG_TYPE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

const RELATIVE_URL_PARSE_BASE = 'https://shopsys.example';

const FRIENDLY_ROUTE_SKELETON_PATTERNS = Object.entries(FriendlyPagesDestinations).map(([pageType, destination]) => ({
    matcher: createRouteMatcher(destination),
    skeletonType: pageType as PageType,
}));

export const getSkeletonTypeFromRouteUrl = (routeUrl: string | undefined): PageType | undefined => {
    if (!routeUrl) {
        return undefined;
    }

    const parsedRouteUrl = getParsedRouteUrl(routeUrl);

    if (!parsedRouteUrl) {
        return undefined;
    }

    const slugType = parsedRouteUrl.searchParams.get(SLUG_TYPE_QUERY_PARAMETER_NAME);
    const friendlyPageType = slugType ? getPageTypeKey(slugType as FriendlyPageTypesValue) : undefined;

    if (friendlyPageType) {
        return friendlyPageType;
    }

    const pathname = normalizePathname(parsedRouteUrl.pathname);

    if (pathname === '/') {
        return SkeletonEnum.Homepage;
    }

    const routePattern = FRIENDLY_ROUTE_SKELETON_PATTERNS.find((pattern) => pattern.matcher.test(pathname));

    return routePattern?.skeletonType;
};

function getParsedRouteUrl(routeUrl: string): URL | undefined {
    try {
        return new URL(routeUrl, RELATIVE_URL_PARSE_BASE);
    } catch {
        return undefined;
    }
}

function normalizePathname(pathname: string): string {
    if (pathname !== '/' && pathname.endsWith('/')) {
        return pathname.slice(0, -1);
    }

    return pathname;
}

function createRouteMatcher(route: string): RegExp {
    const routeRegex = escapeRegExp(route)
        .replaceAll(/\\\[.*?\\\]/g, '[^/]+')
        .replaceAll(/:[^/]+/g, '[^/]+')
        .replaceAll('/', '\\/');

    return new RegExp(`^${routeRegex}$`);
}

function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
