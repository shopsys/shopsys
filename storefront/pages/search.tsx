import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { useSearch } from 'connectors/search/Search';
import { SearchQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
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
import { useGtmSearchResultsListView } from 'hooks/gtm/useGtmSearchResultsListView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useEffect, useMemo } from 'react';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';

const SearchPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const pageParam = router.query[PAGE_QUERY_PARAMETER_NAME];
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const searchProductsSort = getProductListSort(
        parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]),
    );
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const searchParametersFilter = getFilterOptions(
        parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME]),
    );
    const searchQuery = getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME]);
    const searchResults = useSearch(searchQuery, searchProductsSort, paginationCursor, searchParametersFilter);

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Search'), slug: searchUrl }], [t, searchUrl]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('search', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmSearchResultsListView(searchResults, searchQuery);

    useEffect(() => {
        dispatch(
            userActions.setPagination(
                getNewPagination(parsePageNumberFromQuery(pageParam), initialState.pagination.pageSize),
            ),
        );
    }, [dispatch, pageParam]);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout title={t('Search')}>
                <SearchContent searchResults={searchResults} breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]));
    const optionsFilter = getFilterOptions(parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]));
    store.dispatch(
        userActions.setPagination(
            getNewPagination(
                parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]),
                initialState.pagination.pageSize,
            ),
        ),
    );

    return initServerSideProps(context, store, false, [
        {
            query: SearchQueryDocumentApi,
            variables: {
                search: getStringFromUrlQuery(context.query[SEARCH_QUERY_PARAMETER_NAME]),
                orderingMode,
                after: store.getState().user.pagination.paginationCursor,
                filter: mapParametersFilter(optionsFilter),
                first: initialState.pagination.pageSize,
            },
        },
    ]);
});

export default SearchPage;
