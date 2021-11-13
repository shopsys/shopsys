import { EnrichedSearchQueryDocumentApi, NavigationQueryDocumentApi } from 'graphql/generated';
import { FC, useEffect } from 'react';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { getEnrichedSearch } from 'connectors/search/EnrichedSearch';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import SearchPage from 'components/Pages/Search';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';

const Search: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const searchProductsSort = useShopsysSelector((state) => state.user.sort);
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const searchResults = getEnrichedSearch(getParsedSearchQuery(router.query.q), searchProductsSort, paginationCursor);

    useEffect(() => {
        dispatch(userActions.setSort(getProductListSort(parseProductListSortFromQuery(router.query.sort))));
    }, [router.query.sort]);

    useEffect(() => {
        dispatch(userActions.setPagination(getNewPagination(parsePageNumberFromQuery(router.query.page))));
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
    store.dispatch(userActions.setPagination(getNewPagination(parsePageNumberFromQuery(context.query.page))));

    return initServerSideProps(context, store, [
        { query: NavigationQueryDocumentApi },
        {
            query: EnrichedSearchQueryDocumentApi,
            variables: {
                search: getParsedSearchQuery(context.query.q),
                orderingMode: store.getState().user.sort,
                after: store.getState().user.pagination.paginationCursor,
            },
        },
    ]);
});

const getParsedSearchQuery = (searchQuery: string | string[] | undefined): string => {
    if (searchQuery === undefined || Array.isArray(searchQuery)) {
        return '';
    }

    return searchQuery;
};
export default Search;
