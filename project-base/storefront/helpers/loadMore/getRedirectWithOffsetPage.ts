import { getOffsetPageAndLoadMore } from './getOffsetPageAndLoadMore';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getUrlQueriesWithoutDynamicPageQueries } from 'helpers/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { PAGE_QUERY_PARAMETER_NAME, LOAD_MORE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { Redirect } from 'next';
import { ParsedUrlQuery } from 'querystring';

export const getRedirectWithOffsetPage = (
    currentPage: number,
    currentLoadMore: number,
    currentSlug: string,
    currentQuery: ParsedUrlQuery,
    pageSize = DEFAULT_PAGE_SIZE,
): { redirect: Redirect } | undefined => {
    const updatedQueries = getOffsetPageAndLoadMore(currentPage, currentLoadMore, pageSize);

    if (!updatedQueries) {
        return undefined;
    }

    const updatedQuery: ParsedUrlQuery = getUrlQueriesWithoutDynamicPageQueries(currentQuery);
    const searchParams = new URLSearchParams();

    for (const [key, value] of Object.entries(updatedQuery)) {
        if (!value) {
            continue;
        }

        if (Array.isArray(value)) {
            value.forEach((v) => searchParams.append(key, v));
        } else {
            searchParams.set(key, value);
        }
    }

    if (updatedQueries.updatedPage > 1) {
        searchParams.set(PAGE_QUERY_PARAMETER_NAME, updatedQueries.updatedPage.toString());
    } else {
        searchParams.delete(PAGE_QUERY_PARAMETER_NAME);
    }

    if (updatedQueries.updatedLoadMore > 0) {
        searchParams.set(LOAD_MORE_QUERY_PARAMETER_NAME, updatedQueries.updatedLoadMore.toString());
    } else {
        searchParams.delete(LOAD_MORE_QUERY_PARAMETER_NAME);
    }

    return {
        redirect: {
            destination: `${currentSlug}?${searchParams.toString()}`,
            permanent: false,
        },
    };
};
