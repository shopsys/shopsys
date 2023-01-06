import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { useSearch } from 'connectors/search/Search';
import { SearchProductsQueryDocumentApi, SearchQueryDocumentApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
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
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const SearchPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const searchProductsSort = getProductListSort(
        parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]),
    );
    const searchParametersFilter = getFilterOptions(
        parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME]),
    );
    const searchQuery = getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME]);
    const searchResults = useSearch(searchQuery, searchProductsSort, searchParametersFilter);

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Search'), slug: searchUrl }], [t, searchUrl]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('search', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout title={t('Search')}>
                <SearchContent searchResults={searchResults} breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            const orderingMode = getProductListSort(
                parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]),
            );
            const optionsFilter = getFilterOptions(
                parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]),
            );
            const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);
            const filter = mapParametersFilter(optionsFilter);
            const search = getStringFromUrlQuery(context.query[SEARCH_QUERY_PARAMETER_NAME]);

            return initServerSideProps({
                context,
                store,
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
                            endCursor: getNewPagination(page === 0 ? 1 : page).endCursor,
                            pageSize: DEFAULT_PAGE_SIZE,
                        },
                    },
                ],
                redisClient,
            });
        },
        store,
    ),
);

export default SearchPage;
