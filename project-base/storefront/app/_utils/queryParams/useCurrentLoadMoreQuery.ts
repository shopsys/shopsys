'use client';

import { useSearchParams } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentLoadMoreQuery = () => {
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
    const currentLoadMoreQuery = Number(query[LOAD_MORE_QUERY_PARAMETER_NAME] || 0);

    return currentLoadMoreQuery;
};
