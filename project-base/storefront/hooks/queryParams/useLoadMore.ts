import { pushQueries } from './pushQueries';
import { useCurrentLoadMore } from './useCurrentLoadMore';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { LOAD_MORE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';

export const useLoadMore = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentLoadMore = useCurrentLoadMore();

    const loadMore = () => {
        const updatedLoadMore = currentLoadMore + 1;
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: updatedLoadMore > 0 ? updatedLoadMore.toString() : undefined,
        } as const;

        pushQueries(router, newQuery, true);
    };

    return loadMore;
};
