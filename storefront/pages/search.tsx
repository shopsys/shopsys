import MetaRobots from 'components/Basic/Head/MetaRobots';
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
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useEffect, useMemo } from 'react';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { initialState, userActions } from 'redux/slices/user';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';

const Search: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const searchProductsSort = getProductListSort(parseProductListSortFromQuery(router.query.sort));
    const { paginationCursor } = useShopsysSelector((state) => state.user.pagination);
    const searchParametersFilter = getFilterOptions(parseFilterOptionsFromQuery(router.query.filter));
    const searchQuery = useMemo(() => getStringFromUrlQuery(router.query.q), [router.query.q]);
    const searchResults = useSearch(searchQuery, searchProductsSort, paginationCursor, searchParametersFilter);

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Search'), slug: searchUrl }], [t, searchUrl]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('search', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmSearchResultsListView(searchResults, searchQuery);

    useEffect(() => {
        dispatch(
            userActions.setPagination(
                getNewPagination(parsePageNumberFromQuery(router.query.page), initialState.pagination.pageSize),
            ),
        );
    }, [dispatch, router.query.page]);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout title={t('Search')}>
                <SearchPage searchResults={searchResults} breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(context.query.sort));
    const optionsFilter = getFilterOptions(parseFilterOptionsFromQuery(context.query.filter));
    store.dispatch(
        userActions.setPagination(
            getNewPagination(parsePageNumberFromQuery(context.query.page), initialState.pagination.pageSize),
        ),
    );

    return initServerSideProps(context, store, false, [
        {
            query: SearchQueryDocumentApi,
            variables: {
                search: getStringFromUrlQuery(context.query.q),
                orderingMode,
                after: store.getState().user.pagination.paginationCursor,
                filter: mapParametersFilter(optionsFilter),
                first: initialState.pagination.pageSize,
            },
        },
    ]);
});

export default Search;
