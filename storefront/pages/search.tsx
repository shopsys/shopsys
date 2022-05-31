import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import SearchPage from 'components/Pages/Search';
import { useSearch } from 'connectors/search/Search';
import { SearchQueryDocumentApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useGtmSearchResultsListView } from 'hooks/gtm/useGtmSearchResultsListView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useRouter } from 'next/router';
import { FC, useEffect, useMemo } from 'react';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { initialState, userActions } from 'redux/slices/user';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';

const Search: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const searchProductsSort = useShopsysSelector((state) => state.user.sort);
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const optionsFilter = useShopsysSelector((state) => state.optionsFilter);
    const searchQuery = useMemo(() => getStringFromUrlQuery(router.query.q), [router.query.q]);
    const searchResults = useSearch(searchQuery, searchProductsSort, paginationCursor, optionsFilter);

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('search');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmSearchResultsListView(searchResults, searchQuery);

    useEffect(() => {
        dispatch(userActions.setSort(getProductListSort(parseProductListSortFromQuery(router.query.sort))));
    }, [dispatch, router.query.sort]);

    useEffect(() => {
        dispatch(
            userActions.setPagination(
                getNewPagination(parsePageNumberFromQuery(router.query.page), initialState.pagination.pageSize),
            ),
        );
    }, [dispatch, router.query.page]);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <SearchPage searchResults={searchResults} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    store.dispatch(userActions.setSort(getProductListSort(parseProductListSortFromQuery(context.query.sort))));
    store.dispatch(
        userActions.setPagination(
            getNewPagination(parsePageNumberFromQuery(context.query.page), initialState.pagination.pageSize),
        ),
    );
    store.dispatch(
        optionsFilterActions.setOptionsFilter(getFilterOptions(parseFilterOptionsFromQuery(context.query.filter))),
    );

    return initServerSideProps(context, store, false, [
        {
            query: SearchQueryDocumentApi,
            variables: {
                search: getStringFromUrlQuery(context.query.q),
                orderingMode: store.getState().user.sort,
                after: store.getState().user.pagination.paginationCursor,
                filter: mapParametersFilter(store.getState().optionsFilter),
            },
        },
    ]);
});

export default Search;
