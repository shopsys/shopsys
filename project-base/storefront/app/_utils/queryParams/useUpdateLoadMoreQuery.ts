'use client';

import { useCurrentLoadMoreQuery } from './useCurrentLoadMoreQuery';
import { usePushQueries } from './usePushQueries';
import { useSearchParams } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useUpdateLoadMoreQuery = () => {
    const pushQueries = usePushQueries();
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
    const currentLoadMore = useCurrentLoadMoreQuery();

    const updateLoadMoreQuery = () => {
        const updatedLoadMore = currentLoadMore + 1;
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: updatedLoadMore > 0 ? updatedLoadMore.toString() : undefined,
        } as const;

        pushQueries(newQuery, true);
    };

    return updateLoadMoreQuery;
};
