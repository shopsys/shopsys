import Breadcrumbs from 'components/Layout/Breadcrumbs';
import CommonLayout from 'components/Layout/CommonLayout';
import Webline from 'components/Layout/Webline';
import ArticleDetailPage from 'components/Pages/Article';
import BlogArticlePage from 'components/Pages/BlogArticle';
import BlogCategoryPage from 'components/Pages/BlogCategory';
import BrandDetailPage from 'components/Pages/BrandDetail';
import CategoryDetailPage from 'components/Pages/CategoryDetail';
import Error404 from 'components/Pages/ErrorPage/404';
import FlagDetailPage from 'components/Pages/FlagDetail';
import ProductDetailPage from 'components/Pages/ProductDetail';
import ProductDetailMainVariantPage from 'components/Pages/ProductDetail/ProductDetailMainVariant';
import StoreDetailPage from 'components/Pages/StoreDetail';
import { useFriendlyUrlResolvedData } from 'connectors/friendlyUrls/FriendlyUrls';
import { Maybe, SlugQueryApi, SlugQueryDocumentApi, SlugQueryVariablesApi } from 'graphql/generated';
import { createClient } from 'helpers/createClient';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { getSeoTitleAndDescriptionForFriendlyUrlPage } from 'helpers/seo/getSeoTitleAndDescriptionForFriendlyUrlPage';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useGtmBrandProductListView } from 'hooks/gtm/useGtmBrandProductListView';
import { useGtmCategoryProductListView } from 'hooks/gtm/useGtmCategoryProductListView';
import { useGtmFlagProductListView } from 'hooks/gtm/useGtmFlagProductListView';
import { useGtmFriendlyPageView } from 'hooks/gtm/useGtmFriendlyPageView';
import { useGtmProductDetailView } from 'hooks/gtm/useGtmProductDetailView';
import { useRouter } from 'next/router';
import { FC, useEffect } from 'react';
import { nextReduxWrapper, useShopsysDispatch } from 'redux/main';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { initialState, userActions } from 'redux/slices/user';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { MainVariantDetailType, ProductDetailType } from 'types/product';
import { ssrExchange } from 'urql';
import { useGtmPageViewEvent } from 'utils/Gtm/EventFactories';
import { getGtmPageInfoForFriendlyUrl } from 'utils/Gtm/Gtm';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';

const FriendlyUrlPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        dispatch(
            userActions.setPagination(
                getNewPagination(parsePageNumberFromQuery(router.query.page), initialState.pagination.pageSize),
            ),
        );
    }, [dispatch, router.query.page]);

    const slug = getUrlWithoutGetParameters(router.asPath);
    const data = useFriendlyUrlResolvedData(slug);

    const gtmFriendlyUrlPageViewEvent = useGtmPageViewEvent(getGtmPageInfoForFriendlyUrl(data, slug));
    useGtmFriendlyPageView(gtmFriendlyUrlPageViewEvent, slug);
    useGtmCategoryProductListView(data, slug);
    useGtmProductDetailView(data, slug);
    useGtmBrandProductListView(data, slug);
    useGtmFlagProductListView(data, slug);

    return renderContent(data);
};

const renderContent = (data: Maybe<FriendlyUrlPageType>) => {
    switch (data?.__typename) {
        case 'RegularProduct':
        case 'Variant':
            return wrapContent(<ProductDetailPage product={data as ProductDetailType} />, data);
        case 'MainVariant':
            return wrapContent(<ProductDetailMainVariantPage product={data as MainVariantDetailType} />, data);
        case 'Category':
            return wrapContent(<CategoryDetailPage category={data} />, data);
        case 'Store':
            return wrapContent(<StoreDetailPage store={data} />, data);
        case 'Article':
            return wrapContent(<ArticleDetailPage article={data} />, data);
        case 'BlogArticle':
            return wrapContent(<BlogArticlePage blogArticle={data} />, data);
        case 'Brand':
            return wrapContent(<BrandDetailPage brand={data} />, data);
        case 'Flag':
            return wrapContent(<FlagDetailPage flag={data} />, data);
        case 'BlogCategory':
            return wrapContent(<BlogCategoryPage blogCategory={data} />, data);
        default:
            return <Error404 />;
    }
};

const wrapContent = (content: JSX.Element, data: FriendlyUrlPageType) => (
    <CommonLayout {...getSeoTitleAndDescriptionForFriendlyUrlPage(data)}>
        <Webline>
            <Breadcrumbs key="breadcrumb" breadcrumb={data.breadcrumb} />
        </Webline>
        {content}
    </CommonLayout>
);

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(context.query.sort));
    store.dispatch(
        userActions.setPagination(
            getNewPagination(parsePageNumberFromQuery(context.query.page), initialState.pagination.pageSize),
        ),
    );
    store.dispatch(
        optionsFilterActions.setOptionsFilter(getFilterOptions(parseFilterOptionsFromQuery(context.query.filter))),
    );

    const exchange = ssrExchange({ isClient: false });
    const client = await createClient(context, store, exchange);

    const slugQueryVariables: SlugQueryVariablesApi = {
        slug: getUrlWithoutGetParameters(context.resolvedUrl),
        orderingMode,
        endCursorForPagination: store.getState().user.pagination.paginationCursor,
        pageSize: initialState.pagination.pageSize,
        filter: mapParametersFilter(store.getState().optionsFilter),
    };

    const initServerSideData = await initServerSideProps(
        context,
        store,
        false,
        [
            {
                query: SlugQueryDocumentApi,
                variables: slugQueryVariables,
            },
        ],
        client,
        exchange,
    );

    const slugQueryResult = client?.readQuery<SlugQueryApi, SlugQueryVariablesApi>(
        SlugQueryDocumentApi,
        slugQueryVariables,
    );

    if (!slugQueryResult || slugQueryResult.data === undefined) {
        // eslint-disable-next-line require-atomic-updates
        context.res.statusCode = 404;
    }

    return initServerSideData;
});

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split(/(\?|#)/)[0];
};

export default FriendlyUrlPage;
