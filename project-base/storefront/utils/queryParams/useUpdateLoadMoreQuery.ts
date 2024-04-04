import { pushQueries } from './pushQueries';
import { useCurrentLoadMoreQuery } from './useCurrentLoadMoreQuery';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useUpdateLoadMoreQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentLoadMore = useCurrentLoadMoreQuery();

    const updateLoadMoreQuery = () => {
        const updatedLoadMore = currentLoadMore + 1;
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: updatedLoadMore > 0 ? updatedLoadMore.toString() : undefined,
        } as const;

        pushQueries(router, newQuery, true);
    };

    return updateLoadMoreQuery;
};
