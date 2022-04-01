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
import { SlugQueryDocumentApi } from 'graphql/generated';
import { createClient } from 'helpers/createClient';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useGtmCategoryProductListView } from 'hooks/gtm/useGtmCategoryProductListView';
import { useRouter } from 'next/router';
import { FC, useEffect } from 'react';
import { nextReduxWrapper, useShopsysDispatch } from 'redux/main';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { initialState, userActions } from 'redux/slices/user';
import { ArticleDetailType } from 'types/article';
import { BlogArticleDetailType } from 'types/blogArticle';
import { BlogCategoryDetailType } from 'types/blogCategory';
import { BrandDetailType } from 'types/brand';
import { CategoryDetailType } from 'types/category';
import { FlagDetailType } from 'types/flag';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { MainVariantDetailType, ProductDetailType } from 'types/product';
import { StoreDetailType } from 'types/store';
import { ssrExchange } from 'urql';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';

const FriendlyUrlPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();

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

    const slug = getUrlWithoutGetParameters(router.asPath);
    const data = useFriendlyUrlResolvedData(slug);

    useGtmCategoryProductListView(data, slug);

    if (data === null) {
        return <Error404 />;
    }

    return (
        <CommonLayout>
            <Webline>
                <Breadcrumbs key="breadcrumb" breadcrumb={data.breadcrumb} />
            </Webline>
            {renderContent(data)}
        </CommonLayout>
    );
};

function renderContent(data: FriendlyUrlPageType) {
    if (data.__typename === 'RegularProduct' || data.__typename === 'Variant') {
        return <ProductDetailPage product={data as ProductDetailType} />;
    } else if (data.__typename === 'MainVariant') {
        return <ProductDetailMainVariantPage product={data as MainVariantDetailType} />;
    } else if (data.__typename === 'Category') {
        return <CategoryDetailPage category={data as CategoryDetailType} />;
    } else if (data.__typename === 'Store') {
        return <StoreDetailPage store={data as StoreDetailType} />;
    } else if (data.__typename === 'Article') {
        return <ArticleDetailPage article={data as ArticleDetailType} />;
    } else if (data.__typename === 'BlogArticle') {
        return <BlogArticlePage blogArticle={data as BlogArticleDetailType} />;
    } else if (data.__typename === 'Brand') {
        return <BrandDetailPage brand={data as BrandDetailType} />;
    } else if (data.__typename === 'Flag') {
        return <FlagDetailPage flag={data as FlagDetailType} />;
    } else if (data.__typename === 'BlogCategory') {
        return <BlogCategoryPage blogCategory={data as BlogCategoryDetailType} />;
    }

    return <Error404 />;
}

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

    const exchange = ssrExchange({ isClient: false });
    const client = createClient(context, store, exchange);

    const slugQueryVariables = {
        slug: getUrlWithoutGetParameters(context.resolvedUrl),
        sortingMode: store.getState().user.sort,
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

    const slugQueryResult = client?.readQuery(SlugQueryDocumentApi, slugQueryVariables);

    if (!slugQueryResult || slugQueryResult.data === undefined || slugQueryResult.data === null) {
        // eslint-disable-next-line require-atomic-updates
        context.res.statusCode = 404;
    }

    return initServerSideData;
});

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

export default FriendlyUrlPage;
