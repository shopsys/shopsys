import { getStringWithoutLeadingSlash } from './stringWIthoutSlash';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { UrlQueries } from 'hooks/useQueryParams';
import { ParsedUrlQuery } from 'querystring';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';

const ignoredUrlQueries: (string | undefined)[] = Object.values(FriendlyPagesDestinations).map(
    (pagePath) => pagePath.match(/\[(\w+)\]/)?.[1],
);

export const getStringFromUrlQuery = (urlQuery: string | string[] | undefined): string => {
    if (urlQuery === undefined || Array.isArray(urlQuery)) {
        return '';
    }

    return urlQuery;
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

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (!filteredQueries[key] || ignoredUrlQueries.includes(key)) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};

export const getProductListSortFromUrlQuery = (
    sortQuery: string | string[] | undefined,
): ProductOrderingModeEnumApi | null => {
    const sortQueryAsString = getStringFromUrlQuery(sortQuery);

    return Object.values(ProductOrderingModeEnumApi).some((sort) => sort === sortQueryAsString)
        ? (sortQueryAsString as ProductOrderingModeEnumApi)
        : null;
};
