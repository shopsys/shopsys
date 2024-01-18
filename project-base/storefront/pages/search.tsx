import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    BreadcrumbFragmentApi,
    SearchProductsQueryDocumentApi,
    SearchQueryDocumentApi,
    useSearchQueryApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getMappedProductFilter } from 'helpers/filterOptions/getMappedProductFilter';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getRedirectWithOffsetPage } from 'helpers/loadMore';
import {
    getNumberFromUrlQuery,
    getProductListSortFromUrlQuery,
    getSlugFromServerSideUrl,
    getStringFromUrlQuery,
} from 'helpers/parsing/urlParsing';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';

const SearchPage: FC<ServerSidePropsType> = ({ cookies }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { sort, filter, searchString, currentLoadMore } = useQueryParams();

    const [{ data: searchData, fetching }] = useSearchQueryApi({
        variables: {
            search: searchString!,
            orderingMode: sort,
            filter: mapParametersFilter(filter),
            pageSize: DEFAULT_PAGE_SIZE * (currentLoadMore + 1),
        },
        pause: !searchString,
    });

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Search'), slug: searchUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.search_results, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const title = useSeoTitleWithPagination(searchData?.productsSearch.totalCount, t('Search'));

    return (
        <>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout breadcrumbs={breadcrumbs} title={title}>
                <Webline>
                    {searchString ? (
                        <SearchContent fetching={fetching} searchResults={searchData} />
                    ) : (
                        <div className="mb-5 p-12 text-center">
                            <strong>{t('There are no results as you have searched with an empty query...')}</strong>
                        </div>
                    )}
                </Webline>
                <LastVisitedProducts lastVisitedProductsFromCookies={cookies.lastVisitedProducts} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);
    const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 0);
    const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
    const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

    if (redirect) {
        return redirect;
    }

    const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
    const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
    const search = getStringFromUrlQuery(context.query[SEARCH_QUERY_PARAMETER_NAME]);

    return initServerSideProps({
        context,
        prefetchedQueries: !search
            ? []
            : [
                  {
                      query: SearchQueryDocumentApi,
                      variables: {
                          search,
                          orderingMode,
                          filter,
                          pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                      },
                  },
                  {
                      query: SearchProductsQueryDocumentApi,
                      variables: {
                          search,
                          orderingMode,
                          filter,
                          endCursor: getEndCursor(page),
                          pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                      },
                  },
              ],
        redisClient,
        domainConfig,
        t,
    });
});

export default SearchPage;
