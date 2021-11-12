import { FC, useEffect } from 'react';
import { friendlyUrlQuery, getFriendlyUrlResolvedData, isProductType } from 'connectors/friendlyUrls/FriendlyUrls';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch } from 'redux/main';
import ArticleDetailPage from 'components/Pages/Article';
import { ArticleDetailType } from 'connectors/article/types';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import CategoryDetailPage from 'components/Pages/CategoryDetail';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import CommonLayout from 'components/Layout/CommonLayout';
import DefaultErrorPage from 'next/error';
import { getNewPagination } from 'utils/Pagination/getNewPagination';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import { parsePageNumberFromQuery } from 'utils/Pagination/parsePageNumberFromQuery';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import ProductDetailPage from 'components/Pages/ProductDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import StoreDetailPage from 'components/Pages/StoreDetail';
import { StoreDetailType } from 'connectors/stores/types';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import Webline from 'components/Layout/Webline';

const FriendlyUrlPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        dispatch(userActions.setSort(getProductListSort(parseProductListSortFromQuery(router.query.sort))));
    }, [router.query.sort]);

    useEffect(() => {
        dispatch(userActions.setPagination(getNewPagination(parsePageNumberFromQuery(router.query.page))));
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

function renderContent(data: ProductDetailType | CategoryDetailType | StoreDetailType | ArticleDetailType) {
    if (isProductType(data.__typename)) {
        return <ProductDetailPage product={data as ProductDetailType} />;
    } else if (data.__typename === 'Category') {
        return <CategoryDetailPage category={data as CategoryDetailType} />;
    } else if (data.__typename === 'Store') {
        return <StoreDetailPage store={data as StoreDetailType} />;
    } else if (data.__typename === 'Article') {
        return <ArticleDetailPage article={data as ArticleDetailType} />;
    }

    return <DefaultErrorPage statusCode={404} />;
}

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    store.dispatch(userActions.setSort(getProductListSort(parseProductListSortFromQuery(context.query.sort))));
    store.dispatch(userActions.setPagination(getNewPagination(parsePageNumberFromQuery(context.query.page))));

    return initServerSideProps(context, store, [
        { query: NavigationQueryDocumentApi },
        {
            query: friendlyUrlQuery(
                getUrlWithoutGetParameters(context.resolvedUrl),
                store.getState().user.sort,
                store.getState().user.pagination.paginationCursor,
            ),
        },
    ]);
});

const getUrlWithoutGetParameters = (originalUrl: string) => {
    return originalUrl.split('?')[0];
};

export default FriendlyUrlPage;
