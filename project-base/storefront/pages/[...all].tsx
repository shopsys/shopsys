import { getServerSideInternationalizedStaticUrl } from '../helpers/localization/getInternationalizedStaticUrls';
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
import { Error404Content } from 'components/Pages/ErrorPage/Error404Content';
import { FlagDetailContent } from 'components/Pages/FlagDetail/FlagDetailContent';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { ProductDetailMainVariantContent } from 'components/Pages/ProductDetail/ProductDetailMainVariantContent';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import { SlugQueryApi, SlugQueryDocumentApi, SlugQueryVariablesApi, useSlugQueryApi } from 'graphql/generated';
import { getDomainConfig } from 'helpers/domain/domain';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
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
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useSlugQueryDataAsFriendlyUrlPageData } from 'hooks/slug/useSlugQueryDataAsFriendlyUrlPageData';
import { Translate } from 'next-translate';
import { NextRouter, useRouter } from 'next/router';
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
    const friendlyUrlPageData = useSlugQueryDataAsFriendlyUrlPageData(slugData?.slug);
    const pageViewEvent = useGtmFriendlyPageViewEvent(friendlyUrlPageData);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return renderContent(friendlyUrlPageData, fetching, router, t);
};

const renderContent = (
    friendlyUrlPageData: FriendlyUrlPageType | null | undefined,
    fetching: boolean,
    router: NextRouter,
    t: Translate,
) => {
    switch (friendlyUrlPageData?.__typename) {
        case 'RegularProduct':
            return wrapContent(
                <ProductDetailContent product={friendlyUrlPageData} fetching={fetching} />,
                friendlyUrlPageData,
                t,
            );
        case 'MainVariant':
            return wrapContent(
                <ProductDetailMainVariantContent product={friendlyUrlPageData} fetching={fetching} />,
                friendlyUrlPageData,
                t,
            );
        case 'Category':
            return wrapPaginatedContent(
                <CategoryDetailContent category={friendlyUrlPageData} />,
                friendlyUrlPageData,
                router,
                t,
            );
        case 'Store':
            return wrapContent(<StoreDetailContent store={friendlyUrlPageData} />, friendlyUrlPageData, t);
        case 'ArticleSite':
            return wrapContent(<ArticleDetailContent article={friendlyUrlPageData} />, friendlyUrlPageData, t);
        case 'BlogArticle':
            return wrapContent(<BlogArticleDetailContent blogArticle={friendlyUrlPageData} />, friendlyUrlPageData, t);
        case 'Brand':
            return wrapContent(<BrandDetailContent brand={friendlyUrlPageData} />, friendlyUrlPageData, t);
        case 'Flag':
            return wrapContent(<FlagDetailContent flag={friendlyUrlPageData} />, friendlyUrlPageData, t);
        case 'BlogCategory':
            return wrapPaginatedContent(
                <BlogCategoryContent blogCategory={friendlyUrlPageData} />,
                friendlyUrlPageData,
                router,
                t,
            );
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
            {content}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const domainConfig = getDomainConfig(context.req.headers.host!);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]));
    const optionsFilter = getFilterOptions(parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]));
    const ssrCache = ssrExchange({ isClient: false });
    const client = await createClient(context, domainConfig.publicGraphqlEndpoint, ssrCache, redisClient);

    const slugQueryVariables: SlugQueryVariablesApi = {
        slug: getServerSideInternationalizedStaticUrl(context, domainConfig.url).trimmedUrlWithoutQueryParams,
        orderingMode,
        filter: mapParametersFilter(optionsFilter),
    };

    let initServerSideData = await initServerSideProps({
        context,
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
});

export default FriendlyUrlPage;
