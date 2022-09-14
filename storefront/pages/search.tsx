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
                <SearchContent searchResults={searchResults} breadcrumbs={breadcrumbs} />
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

export default SearchPage;
