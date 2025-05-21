import { getOffsetPage } from './getOffsetPage';
import { Redirect } from 'next';
import { ParsedUrlQuery } from 'querystring';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const getRedirectWithOffsetPage = (
    currentPage: number,
    currentLoadMore: number,
    currentSlug: string,
    currentQuery: ParsedUrlQuery,
): { redirect: Redirect } | undefined => {
    const updatedQueries = getOffsetPage(currentPage, currentLoadMore);

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
