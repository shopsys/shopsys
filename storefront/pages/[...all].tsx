import { FC, useEffect } from 'react';
import { initialState, userActions } from 'redux/slices/user';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi, SlugQueryDocumentApi } from 'graphql/generated';
import { nextReduxWrapper, useShopsysDispatch } from 'redux/main';
import ArticleDetailPage from 'components/Pages/Article';
import { ArticleDetailType } from 'types/article';
import { BlogArticleDetailType } from 'types/blogArticle';
import BlogArticlePage from 'components/Pages/BlogArticle';
import BlogCategoryPage from 'components/Pages/BlogCategory';
import { BlogCategoryType } from 'types/blogCategory';
import BrandDetailPage from 'components/Pages/BrandDetail';
import { BrandDetailType } from 'types/brand';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import CategoryDetailPage from 'components/Pages/CategoryDetail';
import { CategoryDetailType } from 'types/category';
import CommonLayout from 'components/Layout/CommonLayout';
import DefaultErrorPage from 'next/error';
import FlagDetailPage from 'components/Pages/FlagDetail';
import { FlagDetailType } from 'types/flag';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { getFriendlyUrlResolvedData } from 'connectors/friendlyUrls/FriendlyUrls';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { MainVariantDetailType } from 'types/product';
import { mapParametersFilter } from 'connectors/categories/Categories';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import ProductDetailMainVariantPage from 'components/Pages/ProductDetail/ProductDetailMainVariant';
import ProductDetailPage from 'components/Pages/ProductDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import StoreDetailPage from 'components/Pages/StoreDetail';
import { StoreDetailType } from 'types/store';
import { useRouter } from 'next/router';
import Webline from 'components/Layout/Webline';

const FriendlyUrlPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();

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

    const data = getFriendlyUrlResolvedData(getUrlWithoutGetParameters(router.asPath));
    if (data === null || data === undefined) {
        return <DefaultErrorPage statusCode={404} />;
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

function renderContent(
    data:
        | ProductDetailType
        | MainVariantDetailType
        | CategoryDetailType
        | StoreDetailType
        | ArticleDetailType
        | BlogArticleDetailType
        | BlogCategoryType
        | BrandDetailType
        | FlagDetailType,
) {
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
        return <BlogCategoryPage blogCategory={data as BlogCategoryType} />;
    }

    return <DefaultErrorPage statusCode={404} />;
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

    return initServerSideProps(context, store, [
        { query: NavigationQueryDocumentApi },
        {
            query: SlugQueryDocumentApi,
            variables: {
                slug: getUrlWithoutGetParameters(context.resolvedUrl),
                sortingMode: store.getState().user.sort,
                endCursorForPagination: store.getState().user.pagination.paginationCursor,
                pageSize: initialState.pagination.pageSize,
                filter: mapParametersFilter(store.getState().optionsFilter),
            },
        },
    ]);
});

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

export default FriendlyUrlPage;
