import { Maybe, useSlugQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { FILTER_QUERY_PARAMETER_NAME, SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useRouter } from 'next/router';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export function useFriendlyUrlResolvedData(slug: string): { data: Maybe<FriendlyUrlPageType>; fetching: boolean } {
    const router = useRouter();
    const categoryDetailSort = getProductListSort(
        parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]),
    );
    const categoryParametersFilter = getFilterOptions(
        parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME]),
    );
    const [{ data, error, fetching }] = useSlugQueryApi({
        variables: {
            slug,
            orderingMode: categoryDetailSort,
            filter: mapParametersFilter(categoryParametersFilter),
        },
    });

    useQueryError(error);

    if (data?.slug?.__typename === undefined) {
        return { data: null, fetching };
    }

    return { data: data.slug as Maybe<FriendlyUrlPageType>, fetching };
}
