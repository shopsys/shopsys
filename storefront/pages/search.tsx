import { FC, useEffect } from 'react';
import { initialState, userActions } from 'redux/slices/user';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi, NotificationBarsDocumentApi, SearchQueryDocumentApi } from 'graphql/generated';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import SearchPage from 'components/Pages/Search';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useRouter } from 'next/router';
import { useSearch } from 'connectors/search/Search';

const Search: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const searchProductsSort = useShopsysSelector((state) => state.user.sort);
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const optionsFilter = useShopsysSelector((state) => state.optionsFilter);
    const searchResults = useSearch(
        getStringFromUrlQuery(router.query.q),
        searchProductsSort,
        paginationCursor,
        optionsFilter,
    );

    useEffect(() => {
        dispatch(userActions.setSort(getProductListSort(parseProductListSortFromQuery(router.query.sort))));
    }, [router.query.sort]);

    useEffect(() => {
        dispatch(
            userActions.setPagination(
                getNewPagination(parsePageNumberFromQuery(router.query.page), initialState.pagination.pageSize),
            ),
        );
    }, [router.query.page]);

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

    return initServerSideProps(context, store, [
        { query: NotificationBarsDocumentApi },
        { query: NavigationQueryDocumentApi },
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
