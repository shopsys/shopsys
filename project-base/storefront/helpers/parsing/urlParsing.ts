import { getStringWithoutLeadingSlash } from './stringWIthoutSlash';
import { ProductOrderingModeEnum } from 'graphql/types';
import { UrlQueries } from 'hooks/useQueryParams';
import { ParsedUrlQuery } from 'querystring';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';

export const getStringFromUrlQuery = (urlQuery: string | string[] | undefined): string => {
    if (urlQuery === undefined || Array.isArray(urlQuery)) {
        return '';
    }

    return urlQuery.trim();
};

export const getNumberFromUrlQuery = (query: string | string[] | undefined, defaultNumber: number): number => {
    const parsedNumber = Number(query);
    return isNaN(parsedNumber) ? defaultNumber : parsedNumber;
};

export const getUrlWithoutGetParameters = (originalUrl: string | undefined): string => {
    return originalUrl?.split(/(\?|#)/)[0] || '';
};

export const getSlugFromUrl = (originalUrl: string): string => {
    return getStringWithoutLeadingSlash(getUrlWithoutGetParameters(originalUrl));
};

export const getSlugFromServerSideUrl = (originalUrl: string): string => {
    const lastUrlSegment = originalUrl.split('/').pop()!;
    const beforeExtensionSegment = lastUrlSegment.split('.')[0];
    const strippedSlug = beforeExtensionSegment.split('?')[0];

    return strippedSlug;
};

export const getQueryWithoutSlugTypeParameterFromParsedUrlQuery = (query: ParsedUrlQuery): ParsedUrlQuery => {
    const routerQueryWithoutAllParameter = { ...query };
    delete routerQueryWithoutAllParameter.slugType;

    return routerQueryWithoutAllParameter;
};

export const getQueryWithoutSlugTypeParameterFromQueryString = (query: string): string => {
    const queryParams = new URLSearchParams(query);
    queryParams.delete('slugType');

    return queryParams.toString();
};

export const getDynamicPageQueryKey = (pathname: string) => {
    const start = pathname.indexOf('[');
    const end = pathname.indexOf(']');

    if (start !== -1 && end !== -1) {
        return pathname.substring(start + 1, end);
    }

    return undefined;
};

export const getUrlQueriesWithoutDynamicPageQueries = (queries: UrlQueries) => {
    const filteredQueries = { ...queries };

    const friendlyPageDynamicSegments = Object.values(FriendlyPagesDestinations).map(
        (pagePath) => pagePath.match(/\[(\w+)\]/)?.[1],
    );

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (friendlyPageDynamicSegments.includes(key)) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};

export const getUrlQueriesWithoutFalsyValues = (queries: UrlQueries) => {
    const filteredQueries = { ...queries };

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (!filteredQueries[key]) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};

export const getProductListSortFromUrlQuery = (
    sortQuery: string | string[] | undefined,
): ProductOrderingModeEnum | null => {
    const sortQueryAsString = getStringFromUrlQuery(sortQuery);

    return Object.values(ProductOrderingModeEnum).some((sort) => sort === sortQueryAsString)
        ? (sortQueryAsString as ProductOrderingModeEnum)
        : null;
};
