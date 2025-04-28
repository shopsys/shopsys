'use client';

import { usePushQueries } from './usePushQueries';
import { useSearchParams } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useUpdatePaginationQuery = () => {
    const pushQueries = usePushQueries();
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;

    const updatePaginationQuery = (page: number) => {
        pushQueryPage(page);
    };

    const pushQueryPage = (page: number) => {
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
            [PAGE_QUERY_PARAMETER_NAME]: page > 1 ? page.toString() : undefined,
        } as const;

        pushQueries(newQuery, true);
    };

    return updatePaginationQuery;
};
