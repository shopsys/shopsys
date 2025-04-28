'use client';

import { useSearchParams } from 'next/navigation';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentPageQuery = () => {
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
    const currentPageQuery = Number(query[PAGE_QUERY_PARAMETER_NAME] || 1);

    return currentPageQuery;
};
