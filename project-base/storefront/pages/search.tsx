import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import {
    BreadcrumbFragmentApi,
    SearchProductsQueryDocumentApi,
    SearchQueryDocumentApi,
    useSearchQueryApi,
} from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useQueryParams } from 'hooks/useQueryParams';
import { GtmPageType } from 'types/gtm/enums';

const SearchPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const { sort, filter, searchString } = useQueryParams();

    const [{ data: searchData, fetching }] = useQueryError(
        useSearchQueryApi({
            variables: {
                search: searchString ?? '',
                orderingMode: sort,
                filter: mapParametersFilter(filter),
                pageSize: DEFAULT_PAGE_SIZE,
            },
        }),
    );

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Search'), slug: searchUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.search_results, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const title = useSeoTitleWithPagination(searchData?.productsSearch.totalCount, t('Search'));

    return (
        <>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout title={title}>
                <SearchContent searchResults={searchData} breadcrumbs={breadcrumbs} fetching={fetching} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const orderingMode = getProductListSort(parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]));
    const optionsFilter = getFilterOptions(parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]));
    const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);
    const filter = mapParametersFilter(optionsFilter);
    const search = getStringFromUrlQuery(context.query[SEARCH_QUERY_PARAMETER_NAME]);

    return initServerSideProps({
        context,
        prefetchedQueries: [
            {
                query: SearchQueryDocumentApi,
                variables: {
                    search,
                    orderingMode,
                    filter,
                    pageSize: DEFAULT_PAGE_SIZE,
                },
            },
            {
                query: SearchProductsQueryDocumentApi,
                variables: {
                    search,
                    orderingMode,
                    filter,
                    endCursor: getEndCursor(page),
                    pageSize: DEFAULT_PAGE_SIZE,
                },
            },
        ],
        redisClient,
    });
});

export default SearchPage;
