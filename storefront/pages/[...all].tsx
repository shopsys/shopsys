import { PaginationProvider } from '../components/Blocks/Pagination/PaginationProvider';
import { getServerSideInternationalizedStaticUrl } from '../helpers/localization/getInternationalizedStaticUrls';
import { getNewPagination } from '../helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from '../helpers/pagination/parsePageNumberFromQuery';
import { useTypedTranslationFunction } from '../hooks/typescript/useTypedTranslationFunction';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleDetailContent } from 'components/Pages/Article/ArticleDetailContent';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import { BlogCategoryContent } from 'components/Pages/BlogCategory/BlogCategoryContent';
import { BrandDetailContent } from 'components/Pages/BrandDetail/BrandDetailContent';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import { Error404Content } from 'components/Pages/ErrorPage/404/Error404Content';
import { FlagDetailContent } from 'components/Pages/FlagDetail/FlagDetailContent';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { ProductDetailMainVariantContent } from 'components/Pages/ProductDetail/ProductDetailMainVariantContent';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import { SlugQueryApi, SlugQueryDocumentApi, SlugQueryVariablesApi, useSlugQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useGtmPageViewEvent } from 'helpers/gtm/eventFactories';
import { getGtmPageInfoForFriendlyUrl } from 'helpers/gtm/gtm';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getSeoTitleAndDescriptionForFriendlyUrlPage } from 'helpers/seo/getSeoTitleAndDescriptionForFriendlyUrlPage';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { createClient } from 'helpers/urql/createClient';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmFriendlyPageView } from 'hooks/gtm/useGtmFriendlyPageView';
import { Translate } from 'next-translate';
import { NextRouter, useRouter } from 'next/router';
import { nextReduxWrapper } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { ssrExchange } from 'urql';

const FriendlyUrlPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const t = useTypedTranslationFunction();
    const categoryDetailSort = getProductListSort(
        parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]),
    );
    const categoryParametersFilter = getFilterOptions(
        parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME]),
    );
    const [{ data: slugData, fetching }] = useQueryError(
        useSlugQueryApi({
            variables: {
                slug,
                orderingMode: categoryDetailSort,
                filter: mapParametersFilter(categoryParametersFilter),
            },
        }),
    );

    const gtmFriendlyUrlPageViewEvent = useGtmPageViewEvent(getGtmPageInfoForFriendlyUrl(slugData?.slug, slug));
    useGtmFriendlyPageView(gtmFriendlyUrlPageViewEvent, slug, fetching);
    return renderContent(slugData?.slug, fetching, router, t);
};

const renderContent = (
    slugData: SlugQueryApi['slug'] | undefined,
    fetching: boolean,
    router: NextRouter,
    t: Translate,
) => {
    switch (slugData?.__typename) {
        case 'RegularProduct':
            return wrapContent(<ProductDetailContent product={slugData} fetching={fetching} />, slugData, t);
        case 'MainVariant':
            return wrapContent(<ProductDetailMainVariantContent product={slugData} fetching={fetching} />, slugData, t);
        case 'Category':
            return wrapPaginatedContent(<CategoryDetailContent category={slugData} />, slugData, router, t);
        case 'Store':
            return wrapContent(<StoreDetailContent store={slugData} />, slugData, t);
        case 'ArticleSite':
            return wrapContent(<ArticleDetailContent article={slugData} />, slugData, t);
        case 'BlogArticle':
            return wrapContent(<BlogArticleDetailContent blogArticle={slugData} />, slugData, t);
        case 'Brand':
            return wrapContent(<BrandDetailContent brand={slugData} />, slugData, t);
        case 'Flag':
            return wrapContent(<FlagDetailContent flag={slugData} />, slugData, t);
        case 'BlogCategory':
            return wrapPaginatedContent(<BlogCategoryContent blogCategory={slugData} />, slugData, router, t);
        default:
            return <Error404Content />;
    }
};

const wrapContent = (content: JSX.Element, slugData: FriendlyUrlPageType, t: Translate) => (
    <CommonLayout {...getSeoTitleAndDescriptionForFriendlyUrlPage(slugData, t)}>
        <Webline>
            <Breadcrumbs key="breadcrumb" breadcrumb={slugData.breadcrumb} />
        </Webline>
        {content}
    </CommonLayout>
);

const wrapPaginatedContent = (content: JSX.Element, data: FriendlyUrlPageType, router: NextRouter, t: Translate) => {
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);

    return (
        <CommonLayout {...getSeoTitleAndDescriptionForFriendlyUrlPage(data, t, currentPage)}>
            <Webline>
                <Breadcrumbs key="breadcrumb" breadcrumb={data.breadcrumb} />
            </Webline>
            <PaginationProvider key={data.uuid} {...getNewPagination(currentPage)}>
                {content}
            </PaginationProvider>
        </CommonLayout>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient, domainConfig) => async (context) => {
            const orderingMode = getProductListSort(
                parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]),
            );
            const optionsFilter = getFilterOptions(
                parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]),
            );
            const ssrCache = ssrExchange({ isClient: false });
            const client = await createClient(context, store, ssrCache, redisClient);

            const slugQueryVariables: SlugQueryVariablesApi = {
                slug: getServerSideInternationalizedStaticUrl(context, domainConfig.url),
                orderingMode,
                filter: mapParametersFilter(optionsFilter),
            };

            let initServerSideData = await initServerSideProps({
                context,
                store,
                prefetchedQueries: [
                    {
                        query: SlugQueryDocumentApi,
                        variables: slugQueryVariables,
                    },
                ],
                client,
                ssrCache,
                redisClient,
            });

            const slugQueryResult = client?.readQuery<SlugQueryApi, SlugQueryVariablesApi>(
                SlugQueryDocumentApi,
                slugQueryVariables,
            );

            if (slugQueryResult?.data?.slug?.__typename === 'Variant') {
                initServerSideData = {
                    redirect: {
                        statusCode: 301,
                        destination: slugQueryResult.data.slug.mainVariant?.slug ?? '/',
                    },
                };
            }

            if (
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                (!slugQueryResult || slugQueryResult.data === undefined || slugQueryResult.data === null) &&
                !(context.res.statusCode === 503)
            ) {
                // eslint-disable-next-line require-atomic-updates
                context.res.statusCode = 404;
            }

            return initServerSideData;
        },
        store,
    ),
);

export default FriendlyUrlPage;
