import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { SearchProductsQueryVariables } from 'graphql/requests/products/queries/SearchProductsQuery.generated';
import { useSearchQuery, SearchQueryVariables } from 'graphql/requests/search/queries/SearchQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { getRedirectWithOffsetPage } from 'helpers/loadMore/getRedirectWithOffsetPage';
import { getNumberFromUrlQuery, getSlugFromServerSideUrl } from 'helpers/parsing/urlParsing';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';

const SearchPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { sort, filter, searchString, currentLoadMore } = useQueryParams();
    const userIdentifier = usePersistStore((state) => state.userId)!;

    const [{ data: searchData, fetching }] = useSearchQuery({
        variables: {
            search: searchString!,
            orderingMode: sort,
            filter: mapParametersFilter(filter),
            pageSize: DEFAULT_PAGE_SIZE * (currentLoadMore + 1),
            isAutocomplete: false,
            userIdentifier,
        },
        pause: !searchString,
    });

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const breadcrumbs: BreadcrumbFragment[] = [{ __typename: 'Link', name: t('Search'), slug: searchUrl }];

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
                <LastVisitedProducts />
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

    return initServerSideProps<SearchQueryVariables | SearchProductsQueryVariables>({
        context,
        redisClient,
        domainConfig,
        t,
    });
});

export default SearchPage;
