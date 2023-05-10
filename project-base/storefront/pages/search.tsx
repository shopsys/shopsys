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
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { GtmPageType } from 'types/gtm/enums';

const SearchPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const { url } = useDomainConfig();
    const searchProductsSort = getProductListSort(
        parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]),
    );
    const searchParametersFilter = getFilterOptions(
        parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME]),
    );
    const searchQuery = getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME]);
    const [{ data: searchData }] = useQueryError(
        useSearchQueryApi({
            variables: {
                search: searchQuery,
                orderingMode: searchProductsSort,
                filter: mapParametersFilter(searchParametersFilter),
                pageSize: DEFAULT_PAGE_SIZE,
            },
        }),
    );

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Search'), slug: searchUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.search_results, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    let title = t('Search');
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    const searchedProductsTotalCount = searchData?.productsSearch.totalCount ?? 0;

    if (searchedProductsTotalCount > DEFAULT_PAGE_SIZE) {
        const additionalPaginationText =
            ' ' +
            t('page {{ currentPage }} from {{ totalPages }}', {
                totalPages: Math.ceil(searchedProductsTotalCount / DEFAULT_PAGE_SIZE),
                currentPage: currentPage,
            });
        title = title + additionalPaginationText;
    }

    return (
        <>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout title={title}>
                <SearchContent searchResults={searchData} breadcrumbs={breadcrumbs} />
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
