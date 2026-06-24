import { NextRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getDynamicPageQueryKey } from 'utils/parsing/getDynamicPageQueryKey';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { getUrlQueriesWithoutFalsyValues } from 'utils/parsing/getUrlQueriesWithoutFalsyValues';

export const pushQueries = (router: NextRouter, queries: UrlQueries, isPush?: boolean, pathnameOverride?: string) => {
    // remove queries which are not set or removed
    const filteredQueries = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(
        getUrlQueriesWithoutDynamicPageQueries(getUrlQueriesWithoutFalsyValues(queries)),
    );

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
