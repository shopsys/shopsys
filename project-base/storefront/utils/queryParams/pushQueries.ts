import { NextRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getDynamicPageQueryKey } from 'utils/parsing/getDynamicPageQueryKey';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { getUrlQueriesWithoutFalsyValues } from 'utils/parsing/getUrlQueriesWithoutFalsyValues';

/**
 * The queries shown in the URL: without empty values, the dynamic page segment and the internal `slugType`.
 * Also used for native links, so opening one in a new tab leads to the same URL as the navigation itself.
 */
export const getQueriesForVisibleUrl = (queries: UrlQueries) =>
    getQueryWithoutSlugTypeParameterFromParsedUrlQuery(
        getUrlQueriesWithoutDynamicPageQueries(getUrlQueriesWithoutFalsyValues(queries)),
    ) as UrlQueries;

export const pushQueries = (router: NextRouter, queries: UrlQueries, isPush?: boolean, pathnameOverride?: string) => {
    const filteredQueries = getQueriesForVisibleUrl(queries);

    const asPathname = router.asPath.split('?')[0];
    const dynamicPageQueryKey = getDynamicPageQueryKey(router.pathname);

    let filteredQueriesWithDynamicParam = filteredQueries;
    if (dynamicPageQueryKey) {
        filteredQueriesWithDynamicParam = {
            [dynamicPageQueryKey]: pathnameOverride || asPathname,
            ...filteredQueries,
        };
    }

    router[isPush ? 'push' : 'replace'](
        {
            pathname: router.pathname,
            query: filteredQueriesWithDynamicParam,
        },
        {
            pathname: pathnameOverride || asPathname,
            query: filteredQueries,
        },
        { shallow: true },
    );
};
